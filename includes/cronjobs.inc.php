<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function cronjobs() {
    global $cfg, $db, $log;

    $log->debug("Begin CronJobs");
    $time_now = time();

    if (($cfg['cron_quarter'] + 900) < $time_now) {
        $db->update('config', ['cfg_value' => $time_now], ['cfg_key' => ['value' => 'cron_quarter']]);
        check_broken_files_linked();
    }

    if (($cfg['cron_hourly'] + 3600) < $time_now) {
        $db->update('config', ['cfg_value' => $time_now], ['cfg_key' => ['value' => 'cron_hourly']]);
        check_masters_childs_integrity();
        update_collections();
        update_people_empty();
        hash_missing();
    }

    if (($cfg['cron_halfday'] + 21600) < $time_now) {
        $db->update('config', ['cfg_value' => $time_now], ['cfg_key' => ['value' => 'cron_halfday']]);
        update_library_stats();
        //delete from wanted orphans (a orphans is create if user delete the torrent outside trackerm
        delete_direct_orphans();
        $cfg['autofix_mediafiles_perms'] ? fix_permissions() : null;
    }
    if (($cfg['cron_daily'] + 8640) < $time_now) {
        $db->update('config', ['cfg_value' => $time_now], ['cfg_key' => ['value' => 'cron_daily']]);
        update_masters();
        check_master_stats();
        update_seasons();
    }

    if (($cfg['cron_weekly'] + 604800) < $time_now) {
        $db->update('config', ['cfg_value' => $time_now], ['cfg_key' => ['value' => 'cron_weekly']]);
//  Not use we update in getMediaData when need
//      clear_tmdb_cache('shows');
    }
    if (($cfg['cron_monthly'] + 2592000) < $time_now) {
        $db->update('config', ['cfg_value' => $time_now], ['cfg_key' => ['value' => 'cron_monthly']]);
//  Not use we update in getMediaData when need
//        clear_tmdb_cache('movies');
        $db->query('VACUUM');
    }
    if ($cfg['cron_update'] == 0) {
        $db->update('config', ['cfg_value' => $time_now], ['cfg_key' => ['value' => 'cron_update']]);
        clean_tmdb_master_entrys('movies');
        clean_tmdb_master_entrys('shows');
        update_masters(true);
    }

    // Upgrading v4 change how clean works, must empty the field and redo, not need know
    // keep for future changes
    //set_clean_titles();
}

function hash_missing() {
    global $db, $log;

    foreach (['movies', 'shows'] as $media_type) {
        $query = $db->query('SELECT id,path FROM library_' . $media_type . ' WHERE file_hash IS NULL OR file_hash IS \'\'');
        $results = $db->fetchAll($query);

        $hashlog = 'Hashing missing ' . $media_type;
        $i = 0;

        foreach ($results as $item) {
            $hash = file_hash($item['path']);
            $update_query = 'update library_' . $media_type . ' SET file_hash=\'' . $hash . '\' WHERE id=\'' . $item['id'] . '\' LIMIT 1';
            $i++;
            $db->query($update_query);
        }
        $log->debug($hashlog . ' (' . $i . ')');
    }
}

function check_broken_files_linked() {
    global $cfg, $log;
    $paths = [];

    $log->debug('Checking broken linked files...');

    if (is_array($cfg['MOVIES_PATH'])) {
        $paths = array_merge($paths, $cfg['MOVIES_PATH']);
    } else if (!empty($cfg['MOVIES_PATH'])) {
        $paths[] = $cfg['MOVIES_PATH'];
    }

    if (is_array($cfg['SHOWS_PATH'])) {
        $paths = array_merge($paths, $cfg['SHOWS_PATH']);
    } else if (!empty($cfg['SHOWS_PATH'])) {
        $paths[] = $cfg['SHOWS_PATH'];
    }
    remove_broken_medialinks($paths, $cfg['media_ext']);
}

function update_seasons($force = false) {
    global $db, $log;

    $log->debug('Executing update_seasons...');

    $update['updated'] = $time_now = time();
    $when_time = time() - 432000; //5 days
    $query = 'SELECT DISTINCT themoviedb_id FROM shows_details';
    (!$force) ? $query .= " WHERE updated < $when_time" : null;
    $query .= ' LIMIT 10';
    $result = $db->query($query);
    $shows = $db->fetchAll($result);
    $i = 0;
    $debug = '';
    if (valid_array($shows)) {
        foreach ($shows as $show) {
            mediadb_getSeasons($show['themoviedb_id']);
            $where['themoviedb_id'] = ['value' => $show['themoviedb_id']];
            $db->update('shows_details', $update, $where);
            $debug .= $show['themoviedb_id'] . ',';
            $i++;
        }
        $log->debug($debug);
    }
    $log->info('Seasons updated:' . $i . ':' . $debug);
}

function delete_direct_orphans() {
    global $trans, $db;

    $items = $db->getItemsByField('wanted', 'direct', 1);
    if (valid_array($trans) && valid_array($items)) {
        $torrents = $trans->getAll();
        if (!valid_array($trans)) {
            return false;
        }

        foreach ($items as $item) {
            $found = 0;
            foreach ($torrents as $torrent) {
                if ($item['hashString'] == $torrent['hashString']) {
                    $found = 1;
                    break;
                }
            }
            (!$found) ? $db->deleteItemById('wanted', $item['id']) : null;
        }
    }
}

function check_masters_childs_integrity() {
    global $db, $log;

    /*
     * check if any master have no childs and childs pointing to a non exists master
     * check child with a missing master
     */


    foreach (['movies', 'shows'] as $media_type) {
        $library_master = 'library_master_' . $media_type;
        $library = 'library_' . $media_type;

        $log->debug('Executing master/childs integrity on ' . $media_type);

        $results = $db->select($library_master, 'id');
        $masters = $db->fetchAll($results);

        $results = $db->select($library, 'id,themoviedb_id,master');
        $childs = $db->fetchAll($results);

        foreach ($masters as $master) {
            if (array_search($master['id'], array_column($childs, 'master')) === false) {
                $log->info('Detected master without childs, removing master id: ' . $master['id']);
                $db->deleteItemById($library_master, $master['id']);
            }
        }

        foreach ($childs as $child) {
            $delete_child = 0;
            if (!empty($child['master'])) {
                if (array_search($child['master'], array_column($masters, 'id')) === false) {
                    $log->info(' Detected an identify child with a missing master (' . $child['master'] . '), delete for reidentify id: ' . $child['id']);
                    $delete_child = 1;
                }
            }
            ($delete_child) ? $db->deleteItemById($library, $child['id']) : null;
        }
    }
}

function clear_tmdb_cache($media_type) {
    global $db, $log;

    $tmdb_cache_table = 'tmdb_search_' . $media_type;
    if ($media_type == 'movies') {
        $time_req = time() - 2592000; // 1 month
    } else {
        $time_req = time() - 604800; // 15 days
    }

    $result = $db->select($tmdb_cache_table, 'id', ['updated' => ['value' => $time_req, 'op' => '<']]);
    $media = $db->fetchAll($result);

    if (valid_array($media)) {
        $log->info('Cleaning tmdb cache ' . $media_type . '(' . count($media) . ')');
        $ids = '';
        $end = end($media);
        foreach ($media as $item) {
            if ($end['id'] == $item['id']) {
                $ids .= $item['id'];
            } else {
                $ids .= $item['id'] . ',';
            }
        }
        $db->query('DELETE FROM ' . $tmdb_cache_table . ' WHERE id IN (' . $ids . ')');
    }
}

function update_masters(bool $force = false) {
    global $db, $log;

    foreach (['shows'] as $media_type) {
        if ($media_type == 'shows') {
            $time_req = time() - 1296000; //15 days
        } else {
            $time_req = time() - 2592000; //1 month
        }
        if (!$force) {
            $where['updated'] = ['value' => $time_req, 'op' => '<'];
        }
        $where['themoviedb_id'] = ['value' => '', 'op' => '<>'];

        $result = $db->select('library_master_' . $media_type, 'id,themoviedb_id', $where);
        $media = $db->fetchAll($result);

        if (valid_array($media)) {
            $log->info('Updating masters ' . $media_type . '(' . count($media) . ')');
            foreach ($media as $item) {
                $update = [];
                $item_cached = mediadb_getMediaData($media_type, $item['themoviedb_id']);
                if (!valid_array($item_cached)) {
                    continue;
                }
                $update['updated'] = time();
                $update['plot'] = $item_cached['plot'];
                $update['rating'] = round($item_cached['rating'], 1);
                $update['popularity'] = isset($item_cached['popularity']) ? round($item_cached['popularity'], 1) : 0;
                $update['poster'] = !empty($item_cached['poster']) ? $item_cached['poster'] : null;
                $update['release'] = $item_cached['release'];
                $update['genres'] = !empty($item_cached['genres']) ? $item_cached['genres'] : null;
                !empty($item_cached['trailer']) ? $update['trailer'] = $item_cached['trailer'] : $update['trailer'] = '';
                !empty($item_cached['collection']) ? $update['collection'] = $item_cached['collection'] : null;
                $media_type == 'shows' ? $update['ended'] = $item_cached['ended'] : null;

                $db->update('library_master_' . $media_type, $update, ['id' => ['value' => $item['id']]]);
            }
        }
    }
}

/*
 * Update tmdb collections based on masters ids
 * Type Groups
 * 1 TMDB Genre
 * 2 Custom Genre
 * 3 TMDB Collection
 * 4 Custom Collection
 */

function update_collections() {
    global $db;

    $media_type = 'movies';

    $results = $db->select('groups', '*', ['type' => ['value' => 3], 'media_type' => ['value' => $media_type]]);
    $groups = $db->fetchAll($results);

    $result = $db->select('library_master_' . $media_type, 'id,collection', ['collection' => ['value' => 0, 'op' => '>']]);
    $masters = $db->fetchAll($result);
    $collections_added = [];

    foreach ($masters as $master) {
        $found = 0;
        if (!in_array($master['collection'], $collections_added)) {
            foreach ($groups as $group) {
                if ($group['type_id'] == $master['collection']) {
                    $found = 1;
                    break;
                }
            }
        } else {
            $found = 1; //already added in this loop;
        }

        if (!$found) {
            $collection = mediadb_getCollection($master['collection']);
            if (valid_array($collection)) {
                $collection['type'] = 3;
                $collection['type_id'] = $master['collection'];
                $collection['media_type'] = 'movies';
                $db->addItem('groups', $collection);
                $collections_added[] = $master['collection'];
            }
        }
    }
}

/*
 * used on upgrades, we clean tmdb cache (entrys related to masters) to force reload
 */

function clean_tmdb_master_entrys($media_type) {
    global $db;

    $library_master = 'library_master_' . $media_type;
    $tmdb_table = 'tmdb_search_' . $media_type;

    $results = $db->select($library_master, 'themoviedb_id');
    $masters = $db->fetchAll($results);
    foreach ($masters as $master) {
        $db->delete($tmdb_table, ['themoviedb_id' => ['value' => $master['themoviedb_id']]], 'LIMIT 1');
    }
}

function update_people_empty() {
    global $db, $log;

    $nupdate = 0;
    $MAX_PERSONS = 10;

    foreach (['movies', 'shows'] as $media_type) {
        $library_master = 'library_master_' . $media_type;
        $results = $db->query("SELECT id,title,themoviedb_id FROM $library_master WHERE \"cast\" IS NULL OR \"cast\" = \"\" LIMIT 20");
        $masters = $db->fetchAll($results);

        if (valid_array($masters)) {
            foreach ($masters as $master) {
                $cast = [];
                $director = [];
                $writer = [];
                $people = mediadb_getPeople($media_type, $master['themoviedb_id']);
                //CAST
                $people_counter = 0;
                if (valid_array($people) && valid_array($people['cast'])) {
                    foreach ($people['cast'] as $people_cast) {
                        $cast[] = $people_cast['name'];
                        $people_counter++;
                        if ($people_counter == $MAX_PERSONS) {
                            break;
                        }
                    }
                }
                //CREW
                $director_counter = 0;
                $writing_counter = 0;
                if (valid_array($people) && valid_array($people['crew'])) {
                    foreach ($people['crew'] as $people_crew) {
                        //DIRECTOR
                        if ($media_type == 'movies') {
                            if (!empty($people_crew['department']) && $people_crew['department'] == 'Directing' && !empty($people_crew['job']) && $people_crew['job'] == 'Director' && $director_counter < $MAX_PERSONS) {
                                $director[] = $people_crew['name'];
                                $director_counter++;
                            }
                        } else {
                            if (!empty($people_crew['department']) && $people_crew['department'] == 'Directing' && !empty($people_crew['jobs']) && valid_array($people_crew['jobs']) && !empty($people_crew['jobs'][0]['job']) && $people_crew['jobs'][0]['job'] == 'Director' && $director_counter < $MAX_PERSONS) {
                                $director[] = $people_crew['name'];
                                $director_counter++;
                            }
                        }
                        //WRITER
                        if (!empty($people_crew['department']) && $people_crew['department'] == 'Writing' && $writing_counter < $MAX_PERSONS) {
                            $writer[] = $people_crew['name'];
                            $writing_counter++;
                        }
                    }
                }

                //UPDATE
                $cast = array_unique($cast);
                $director = array_unique($director);
                $writer = array_unique($writer);

                $update['cast'] = implode(',', $cast);
                $update['director'] = implode(',', $director);
                $update['writer'] = implode(',', $writer);
                $nupdate++;
                $db->updateItemById($library_master, $master['id'], $update);
            }
        }
    }
    $log->debug("Update master media people " . $nupdate);
}

function fix_permissions() {
    global $cfg, $log;

    $paths = [];

    $log->info("Starting Fix Permissions");
    if (empty($cfg['files_usergroup']) || empty($cfg['dir_perms']) || empty($cfg['files_perms'])) {
        $log->warning("You must set the files usergroup, dir/file perms in settigns for auto fix permissions");
        return false;
    }

    if (is_array($cfg['MOVIES_PATH'])) {
        $paths = array_merge($paths, $cfg['MOVIES_PATH']);
    } else if (!empty($cfg['MOVIES_PATH'])) {
        $paths[] = $cfg['MOVIES_PATH'];
    }

    if (is_array($cfg['SHOWS_PATH'])) {
        $paths = array_merge($paths, $cfg['SHOWS_PATH']);
    } else if (!empty($cfg['SHOWS_PATH'])) {
        $paths[] = $cfg['SHOWS_PATH'];
    }

    foreach ($paths as $path) {
        if ($cfg['autofix_mediafiles_perms_all']) {
            $allfiles = get_dir_contents($path);
        } else {
            $allfiles = find_media_files($path, $cfg['media_ext']);
        }

        foreach ($allfiles as $file) {
            if (is_dir($file)) {
                if (!(chgrp($file, $cfg['files_usergroup'])) || !chmod($file, octdec("0" . $cfg['dir_perms']))) {
                    $log->warning("Fix perms chgrp/chmod fail on directory $file");
                }
            } else {
                if (!(chgrp($file, $cfg['files_usergroup'])) || !chmod($file, octdec("0" . $cfg['files_perms']))) {
                    $log->warning("Fix perms chgrp/chmod fail on file $file");
                }
            }
        }
    }
}
