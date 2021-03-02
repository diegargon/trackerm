<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function rebuild($media_type, $paths) {
    global $log;
    //r_blocker prevent forever locks, if more than 3 consecutive locks (probably get stuck, resetting)
    if (($r_blocker = getPrefsItem('rebuild_blocker', true)) && $r_blocker <= 3) {
        setPrefsItem('rebuild_blocker', ++$r_blocker, true);
        $log->warning("Rebuild: blocked ($r_blocker)");
        return false;
    }
    setPrefsItem('rebuild_blocker', 1, true);

    if (valid_array($paths)) {
        foreach ($paths as $path) {
            _rebuild($media_type, $path);
            sleep(1);
        }
    } else {
        _rebuild($media_type, $paths);
    }

    setPrefsItem('rebuild_blocker', 0, true);
}

function _rebuild($media_type, $path) {
    global $cfg, $db, $log, $LNG;

    $log->debug("Rebuild $media_type called");
    $items = [];
    $files = findMediaFiles($path, $cfg['media_ext']);

    $library_table = 'library_' . $media_type;

    $media = $db->getTableData($library_table);
    $i = 0;

    //check if each media path it in $files if not probably delete, then delete db entry
    //this avoid problems if the file was moved
    (valid_array($media)) ? clean_database($media_type, $files, $media) : null;

    foreach ($files as $file) {
        if (!valid_array($media) ||
                array_search($file, array_column($media, 'path')) === false
        ) {
            $file_name = trim(basename($file));
            $predictible_title = getFileTitle($file_name);
            $year = getFileYear($file_name);
            $tags = getFileTags($file_name);
            $ext = substr($file_name, -3);
            if (file_exists($file)) {
                $hash = file_hash($file);
                $filesize = filesize($file);
            } else {
                $hash = null;
                $filesize = null;
            }

            $items[$i] = [
                'file_name' => $file_name,
                'size' => $filesize,
                'predictible_title' => ucwords($predictible_title),
                'title' => '',
                'title_year' => $year,
                'file_hash' => $hash,
                'path' => $file,
                'tags' => $tags,
                'ext' => $ext,
            ];

            if ($media_type == 'shows') {
                $SE = getFileEpisode($file_name);
                if (!empty($SE)) {
                    $season = intval($SE['season']);
                    $episode = intval($SE['episode']);
                    $items[$i]['season'] = $season;
                    $items[$i]['episode'] = $episode;
                } else {
                    $msg_log = '[' . $LNG['L_ERROR'] . '] ' . $LNG['L_ERR_SE'] . ' ' . $items[$i]['file_name'];
                    $log->addStateMsg($msg_log);
                    $log->warning($msg_log);
                    $items[$i]['season'] = 'X';
                    $items[$i]['episode'] = 'X';
                }
            }
        }
        $i++;
    }
    if (valid_array($items)) {
        $insert_ids = $db->addItems($library_table, $items);
        //If is a show we check if already have other episodes identified to identify this. if unset ids
        if ($media_type == 'shows' && valid_array($media)) {
            $insert_ids = ident_by_already_have_show($media, $insert_ids);
        }
        //We check history for auto identify in we had that file. if unset ids
        valid_array($insert_ids) ? $insert_ids = check_history($media_type, $insert_ids) : null;
        //last if auto_identify is on we check title agains online db. if unset ids
        if (!empty($cfg['auto_identify'])) {
            (valid_array($insert_ids)) ? auto_ident_exact($media_type, $insert_ids) : null;
        }
    }

    return true;
}

function ident_by_already_have_show($media, $ids) {
    global $log, $db;

    foreach ($ids as $id_key => $id) {
        $log->debug("ident_by_already_have_show called for id $id");
        $item = $db->getItemById('library_shows', $id);
        foreach ($media as $id_item) {
            if ($id_item['predictible_title'] === ucwords($item['predictible_title']) &&
                    !empty($id_item['themoviedb_id'])
            ) {
                $item['themoviedb_id'] = $id_item['themoviedb_id'];
                $item['title'] = $id_item['title'];
                $item['clean_title'] = clean_title($id_item['title']);
                $item['poster'] = $id_item['poster'];
                $item['rating'] = $id_item['rating'];
                $item['popularity'] = $id_item['popularity'];
                $item['scene'] = $id_item['scene'];
                $item['lang'] = $id_item['lang'];
                $item['plot'] = $id_item['plot'];
                isset($id_item['trailer']) ? $item['trailer'] = $id_item['trailer'] : null;
                $item['original_title'] = $id_item['original_title'];
                submit_ident('shows', $item, $id);
                unset($ids[$id_key]);
                $log->debug('Ident by already have show ' . $item['id'] . ' ' . $item['title'] . ' ' . $item['season'] . 'E' . $item['episode']);
                break;
            }
        }
    }
    return $ids;
}

function show_identify_media($media_type) {
    global $LNG, $cfg, $db, $filter;

    $titles = '';
    $i = 0;
    $uniq_shows = [];
    $iurl = '?page=' . $filter->getString('page');

    if ($media_type == 'shows') {
        $result = $db->query("SELECT * FROM library_$media_type WHERE title = '' OR title is NULL");
    } else {
        $result = $db->query("SELECT * FROM library_$media_type WHERE title = '' OR title is NULL");
    }
    $media = $db->fetchAll($result);
    if (!valid_array($media)) {
        return false;
    }

    if ($media_type == 'shows') {
        foreach ($media as $item) {
            if ((array_search($item['predictible_title'], $uniq_shows)) === false) {
                $uniq_shows[] = $item['predictible_title'];
                $media_tmp[] = $item;
            }
        }
        $media = $media_tmp;
    }

    if (!empty($cfg['auto_identify'])) {
        foreach ($media as $auto_id_item) {
            if (empty($auto_id_item['title']) || empty($auto_id_item['themoviedb_id'])) {
                $auto_id_ids[] = $auto_id_item['id'];
            }
        }
        (isset($auto_id_ids) && count($auto_id_ids) > 0 ) ? auto_ident_exact($media_type, $auto_id_ids) : null;
        //Need requery for failed automate ident, probably there is a better way TODO
        $result = $db->query("SELECT * FROM library_$media_type WHERE title = '' OR title is NULL");
        $media = $db->fetchAll($result);
        if (empty($media)) {
            return false;
        }
    }

    $uniq_shows = [];
    foreach ($media as $item) {
        $title_tdata['results_opt'] = '';

        if (empty($item['title'])) {
            if ($i >= $cfg['max_identify_items']) {
                break;
            }
            if ($media_type == 'movies') {
                $db_media = mediadb_searchMovies($item['predictible_title']);
            } else if ($media_type == 'shows') {
                if ((array_search($item['predictible_title'], $uniq_shows)) === false) {
                    $db_media = mediadb_searchShows($item['predictible_title']);
                    $uniq_shows[] = $item['predictible_title'];
                } else {
                    continue;
                }
            } else {
                return false;
            }

            if (valid_array($db_media)) {
                foreach ($db_media as $db_item) {
                    $year = trim(substr($db_item['release'], 0, 4));
                    $title_tdata['results_opt'] .= '<option value="' . $db_item['themoviedb_id'] . '">';
                    $title_tdata['results_opt'] .= $db_item['title'];
                    !empty($year) ? $title_tdata['results_opt'] .= ' (' . $year . ')' : null;
                    $title_tdata['results_opt'] .= '</option>';
                }
            }
            $title_tdata['del_iurl'] = $iurl . '&media_type=' . $media_type . '&ident_delete=' . $item['id'];
            $title_tdata['more_iurl'] = '?page=identify&media_type=' . $media_type . '&identify=' . $item['id'];
            $title_tdata['media_type'] = $media_type;
            $titles .= $table = getTpl('identify_item', array_merge($item, $title_tdata));
            $i++;
        }
    }

    if (!empty($titles)) {
        $tdata['titles'] = $titles;
        $tdata['head'] = $LNG['L_IDENT_' . strtoupper($media_type) . ''];

        $table = getTpl('identify', $tdata);

        return $table;
    }
    return false;
}

function auto_ident_exact($media_type, $ids) {
    global $log, $db, $cfg;

    if (!valid_array($ids) || empty($media_type)) {
        return false;
    }
    $uniq_shows = [];

    foreach ($ids as $id) {
        $log->debug("auto_ident by exact called for $id");
        $db_item = $db->getItemById('library_' . $media_type, $id);
        if ($media_type == 'movies') {
            $search_media = mediadb_searchMovies($db_item['predictible_title']);
        } else if ($media_type == 'shows') {
            if ((array_search($db_item['predictible_title'], $uniq_shows)) === false) {
                $search_media = mediadb_searchShows($db_item['predictible_title']);
                $uniq_shows[] = $db_item['predictible_title'];
            } else {
                $log->debug('Already identifyed by first show match');
                continue;
            }
        } else {
            return false;
        }

        if (!empty($search_media[0]['themoviedb_id'])) {
            $found = 0;
            foreach ($search_media as $coincidence) {
                $coincidence_title = clean_title($coincidence['title']);
                $db_item_title = clean_title($db_item['predictible_title']);
                if ($coincidence_title == $db_item_title) {
                    submit_ident($media_type, $coincidence, $id);
                    $found = 1;
                    break;
                }
            }
            if (!$found && !$cfg['auto_ident_strict']) {
                $found = 1;
                submit_ident($media_type, $search_media[0], $id);
            }
        } else {
            continue;
        }
        !$found ? $log->debug("Auto ident is set but titles not match $db_item_title") : null;
    }

    return true;
}

function ident_by_idpairs($media_type, $id_pairs) {
    global $log;

    $log->debug("Ident by idpairs called");
    if (!valid_array($id_pairs)) {
        return false;
    }
    foreach ($id_pairs as $my_id => $tmdb_id) {
        (!empty($my_id) && !emptY($tmdb_id)) ? ident_by_id($media_type, $tmdb_id, $my_id) : null;
    }

    return true;
}

function ident_by_id($media_type, $tmdb_id, $id) {
    global $log;

    $log->debug("Ident by ident_by_id called");
    $db_data = mediadb_getFromCache($media_type, $tmdb_id);
    if (valid_array($db_data)) {
        submit_ident($media_type, $db_data, $id);
    } else {
        return false;
    }

    return true;
}

function submit_ident($media_type, $item, $id) {
    global $db, $log;

    $log->debug("Submit $media_type ident : " . $item['title'] . ' id:' . $id);

    $upd_fields = [];
    $where_check['themoviedb_id'] = ['value' => $item['themoviedb_id']];
    if ($media_type == 'shows') {
        $show_check = $db->getItemById('library_shows', $id);
        if (!empty($show_check['season']) && !empty($show_check['episode'])) {
            $where_check['season'] = ['value' => $show_check['season']];
            $where_check['episode'] = ['value' => $show_check['episode']];
        } else {
            $log->debug("submit_ident: its show but havent season/episode set " . $id);
            return false;
        }
    }

    $q_results = $db->select('library_' . $media_type, '*', $where_check, 'LIMIT 1');
    $dup_result = $db->fetchAll($q_results);
    if (valid_array($dup_result)) {
        $ep = '';
        isset($item['season']) ? $ep .= 'S' . $item['season'] : null;
        isset($item['episode']) ? $ep .= 'E' . $item['episode'] : null;
        $log->debug('Discarding item, already identifyied or duplicate ' . $item['title'] . ' ' . $ep);
        return false;
    }

    //CHANGE BEHAVIOUR
    /*
     * For the next change going to use to identify library_master_* as parent of library_shows/movies
     * This table would keep the identification of the show and ids for the episodes pointing to library_shows that will keep
     * the file related things only and drop all identification fields.
     * First task will only create and populate the table but behaviour will continue unsing library_shows for avoid
     * intrudece a big and untested change.
     * The idea for do it on movies is the future ability of have multiple qualitys movies.
     */

    $media_master = $db->getItemByField('library_master_' . $media_type, 'themoviedb_id', $item['themoviedb_id']);
    $_item = $item; //to remove, now not want modify item since we use later in actual behaviour.

    if (valid_array($media_master)) {
        $media_in_library = $db->getItemById('library_' . $media_type, $id);
        if (empty($media_in_library['master'])) {
            $total_items = $media_master['total_items'] + 1;
            $total_size = $media_master['total_size'] + $media_in_library['size'];
            $db->update('library_master_' . $media_type, ['total_items' => $total_items, 'total_size' => $total_size], ['id' => ['value' => $media_master['id']]]);
            $media_in_library = $db->getItemById('library_' . $media_type, $id);
            $db->update('library_' . $media_type, ['master' => $media_master['id']], ['predictible_title' => ['value' => $media_in_library['predictible_title']]]);
        }
    } else {
        $media_in_library = $db->getItemById('library_' . $media_type, $id);
        $_item['total_items'] = 1;
        $_item['total_size'] = $media_in_library['size'];
        unset($_item['id']);
        unset($_item['ilink']);
        unset($_item['elink']);
        unset($_item['in_library']);
        unset($_item['added']);
        unset($_item['created']);
        unset($_item['file_hash']);
        unset($_item['media_info']);
        if (empty($media_in_library['master'])) {
            $db->insert('library_master_' . $media_type, $_item);
            $lastid_master = $db->getLastId();
            $db->update('library_' . $media_type, ['master' => $lastid_master], ['predictible_title' => ['value' => $media_in_library['predictible_title']]]);
        } else {
            $db->update('library_master_' . $media_type, $_item, ['id' => ['value' => $media_in_library['master']]]);
        }
    }
    /*
     *
     * FIN CHANGE
     *
     */

    if (!empty($item['title'])) {
        $upd_fields['title'] = $item['title'];
        $upd_fields['clean_title'] = clean_title($item['title']);
    }
    if (!empty($item['name'])) {
        $upd_fields['name'] = $item['name'];
        $upd_fields['clean_title'] = clean_title($item['name']);
    }
    $upd_fields['themoviedb_id'] = $item['themoviedb_id'];
    !empty($item['poster']) ? $upd_fields['poster'] = $item['poster'] : $upd_fields['poster'] = '';
    !empty($item['original_title']) ? $upd_fields['original_title'] = $item['original_title'] : $upd_fields['original_title'] = '';
    !empty($item['rating']) ? $upd_fields['rating'] = $item['rating'] : $upd_fields['rating'] = '';
    !empty($item['popularity']) ? $upd_fields['popularity'] = $item['popularity'] : $upd_fields['popularity'] = '';
    !empty($item['scene']) ? $upd_fields['scene'] = $item['scene'] : $upd_fields['scene'] = '';
    !empty($item['lang']) ? $upd_fields['lang'] = $item['lang'] : $upd_fields['lang'] = '';
    !empty($item['trailer']) ? $upd_fields['trailer'] = $item['trailer'] : $upd_fields['trailer'] = '';
    !empty($item['plot']) ? $upd_fields['plot'] = $item['plot'] : $upd_fields['plot'] = '';
    !empty($item['release']) ? $upd_fields['release'] = $item['release'] : $upd_fields['release'] = '';

    if ($media_type == 'movies') {
        $db->updateItemById('library_movies', $id, $upd_fields);
    } else if ($media_type == 'shows') {
        $mylib_shows = $db->getItemById('library_shows', $id);
        if (valid_array($mylib_shows)) {
            $where['predictible_title'] = ['value' => $mylib_shows['predictible_title']];
            $db->update('library_shows', $upd_fields, $where);
        } else {
            return false;
        }
    }

    return true;
}

function check_history($media_type, $ids) {
    global $db, $log;

    if (!valid_array($ids)) {
        return false;
    }
    ($media_type == 'movies') ? $library = 'library_movies' : $library = 'library_shows';

    foreach ($ids as $id_key => $id) {
        if (!is_numeric($id)) {
            continue;
        }
        $item = $db->getItemById($library, $id);
        if (empty($item['file_hash'])) {
            continue;
        }
        if (empty($item['themoviedb_id'])) {
            $where = [
                'media_type' => ['value' => $media_type],
                'file_hash' => ['value' => $item['file_hash']],
            ];

            $results = $db->select('library_history', 'themoviedb_id', $where, 'LIMIT 1');
            $item_history = $db->fetch($results);
            $db->finalize($results);
            if (valid_array($item_history) && !empty($item_history['themoviedb_id'])) {
                $log->debug("Identified item by history: tmdb id {$item_history['themoviedb_id']} ");
                ident_by_id($media_type, $item_history['themoviedb_id'], $id);
                unset($ids[$id_key]);
            }
        }
    }
    return $ids;
}

function getLibraryStats() {
    global $cfg;

    $stats['num_movies'] = $cfg['stats_movies'];
    $stats['num_shows'] = $cfg['stats_shows'];
    $stats['num_episodes'] = $cfg['stats_shows_episodes'];
    $stats['movies_size'] = $cfg['stats_total_movies_size'];
    $stats['shows_size'] = $cfg['stats_total_shows_size'];

    if (is_array($cfg['MOVIES_PATH'])) {
        foreach ($cfg['MOVIES_PATH'] as $movies_path) {
            $movies_path_name = basename($movies_path);
            $movies_free_space = human_filesize(disk_free_space($movies_path));
            $movies_total_space = human_filesize(disk_total_space($movies_path));
            $stats['movies_paths'][$movies_path]['free'] = $movies_free_space;
            $stats['movies_paths'][$movies_path]['total'] = $movies_total_space;
            $stats['movies_paths'][$movies_path]['basename'] = $movies_path_name;
        }
    } else {
        $stats['movies_paths'][$cfg['MOVIES_PATH']]['free'] = human_filesize(disk_free_space($cfg['MOVIES_PATH']));
        $stats['movies_paths'][$cfg['MOVIES_PATH']]['total'] = human_filesize(disk_total_space($cfg['MOVIES_PATH']));
        $stats['movies_paths'][$cfg['MOVIES_PATH']]['basename'] = basename($cfg['MOVIES_PATH']);
    }

    if (is_array($cfg['SHOWS_PATH'])) {
        foreach ($cfg['SHOWS_PATH'] as $shows_path) {
            $shows_path_name = basename($shows_path);
            $shows_free_space = human_filesize(disk_free_space($shows_path));
            $shows_total_space = human_filesize(disk_total_space($shows_path));
            $stats['shows_paths'][$shows_path]['free'] = $shows_free_space;
            $stats['shows_paths'][$shows_path]['total'] = $shows_total_space;
            $stats['shows_paths'][$shows_path]['basename'] = $shows_path_name;
        }
    } else {
        $stats['movies_paths'][$cfg['SHOWS_PATH']]['free'] = human_filesize(disk_free_space($cfg['SHOWS_PATH']));
        $stats['movies_paths'][$cfg['SHOWS_PATH']]['total'] = human_filesize(disk_total_space($cfg['SHOWS_PATH']));
        $stats['movies_paths'][$cfg['SHOWS_PATH']]['basename'] = basename($cfg['SHOWS_PATH']);
    }

    $stats['db_size'] = file_exists($cfg['DB_FILE']) ? human_filesize(filesize($cfg['DB_FILE'])) : 0;

    return $stats;
}

function clean_database($media_type, $files, $media) {
    global $log, $db, $LNG;

    $last_oid = 0;
    foreach ($media as $item) {
        if (!in_array($item['path'], $files)) {
            if ($last_oid != $item['themoviedb_id']) { //avoid spam on shows deleted
                $log->addStateMsg('[' . $LNG['L_NOTE'] . '] ' . $item['title'] . ' ' . $LNG['L_NOTE_MOVDEL']);
                $last_oid = $item['themoviedb_id'];
            }
            if (isset($item['themoviedb_id'])) {
                $values['title'] = $item['title'];
                $values['themoviedb_id'] = $item['themoviedb_id'];
                $values['clean_title'] = clean_title($item['title']);
                $values['media_type'] = $media_type;
                $values['file_name'] = $item['file_name'];
                $values['custom_poster'] = $item['custom_poster'];
                $values['size'] = $item['size'];
                $values['file_hash'] = $item['file_hash'];
                isset($item['season']) ? $values['season'] = $item['season'] : null;
                isset($item['episode']) ? $values['episode'] = $item['episode'] : null;
                $item_hist_id = $db->getIdByField('library_history', 'file_hash', $item['file_hash']);
                if (!$item_hist_id) {
                    $db->insert('library_history', $values);
                } else {
                    $db->update('library_history', $values, ['id' => ['value' => $item_hist_id]], 'LIMIT 1');
                }
            }

            $master = $db->getItemById('library_master_' . $media_type, $item['master']);
            if (valid_array($master)) {
                if ($master['total_items'] > 1) {
                    $new_size = $master['total_size'] - $item['size'];
                    $new_total = $master['total_items'] - 1;
                    $db->updateItemById('library_master_' . $media_type, ['total_size' => $new_size, 'total_items' => $new_total], ['id' => $master['id']]);
                } else {
                    $db->deleteItemById('library_master_' . $media_type, $master['id']);
                }
            }

            $db->deleteItemById('library_' . $media_type, $item['id']);
        }
    }
}

function update_master_stats() {
    global $db, $log;

    $master_movies = $db->getTableData('library_master_movies');

    if (valid_array($master_movies)) {
        foreach ($master_movies as $master_movie) {
            $total_size = 0;
            $set = [];
            $items = $db->getItemsByField('library_movies', 'master', $master_movie['id']);
            $num_items = count($items);

            if (valid_array($items)) {
                foreach ($items as $item) {
                    $total_size = $total_size + $item['size'];
                }

                if ($master_movie['total_size'] != $total_size) {
                    $log->debug("Discrepancy on master size {$master_movie['title']} $total_size:{$master_movie['total_size']}");
                    $set['total_size'] = $total_size;
                }
                if ($master_movie['total_items'] != $num_items) {
                    $log->debug("Discrepancy on master items {$master_movie['title']} $num_items:{$master_movie['total_items']}");
                    $set['total_items'] = $num_items;
                }
                if (valid_array($set)) {
                    $db->update('library_master_movies', $set, ['id' => ['value' => $master_movie['id']]]);
                }
            }
        }
    }

    $master_shows = $db->getTableData('library_master_shows');

    if (valid_array($master_shows)) {
        foreach ($master_shows as $master_show) {
            $total_size = 0;
            $set = [];
            $items = $db->getItemsByField('library_shows', 'master', $master_show['id']);

            if (valid_array($items)) {
                $num_items = count($items);
                foreach ($items as $item) {
                    $total_size = $total_size + $item['size'];
                }

                if ($master_show['total_size'] != $total_size) {
                    $log->debug("Discrepancy on master size {$master_show['title']} $total_size:{$master_show['total_size']}");
                    $set['total_size'] = $total_size;
                }
                if ($master_show['total_items'] != $num_items) {
                    $log->debug("Discrepancy on master items {$master_show['title']} $num_items:{$master_show['total_items']}");
                    $set['total_items'] = $num_items;
                }
                if (valid_array($set)) {
                    $db->update('library_master_shows', $set, ['id' => ['value' => $master_show['id']]]);
                }
            }
        }
    }
}
