<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function cronjobs() {
    global $cfg, $db;

    $time_now = time();

    if (($cfg['cron_quarter'] + 900) < $time_now) {
        $db->update('config', ['cfg_value' => $time_now], ['cfg_key' => ['value' => 'cron_quarter']]);
        check_broken_files_linked();
    }

    if (($cfg['cron_hourly'] + 3600) < $time_now) {
        $db->update('config', ['cfg_value' => $time_now], ['cfg_key' => ['value' => 'cron_hourly']]);
        check_masters_childs_integrity();
    }

    if (($cfg['cron_halfday'] + 21600) < $time_now) {
        $db->update('config', ['cfg_value' => $time_now], ['cfg_key' => ['value' => 'cron_halfday']]);
        update_library_stats();
        hash_missing();
        //delete from wanted orphans (a orphans is create if user delete the torrent outside trackerm
        delete_direct_orphans();
    }
    if (($cfg['cron_daily'] + 8640) < $time_now) {
        $db->update('config', ['cfg_value' => $time_now], ['cfg_key' => ['value' => 'cron_daily']]);
        update_masters();
        check_master_stats();
        update_seasons();
    }

    if (($cfg['cron_weekly'] + 604800) < $time_now) {
        $db->update('config', ['cfg_value' => $time_now], ['cfg_key' => ['value' => 'cron_weekly']]);
        clear_tmdb_cache('shows');
    }
    if (($cfg['cron_monthly'] + 2592000) < $time_now) {
        $db->update('config', ['cfg_value' => $time_now], ['cfg_key' => ['value' => 'cron_monthly']]);
        clear_tmdb_cache('movies');
        $db->query('VACUUM');
    }
    if ($cfg['cron_update'] == 0) {
        $db->update('config', ['cfg_value' => $time_now], ['cfg_key' => ['value' => 'cron_update']]);
        update_masters(true);
    }

    // Upgrading v4 change how clean works, must empty the field and redo, not need know
    // keep for future changes
    //set_clean_titles();
}

function hash_missing() {
    global $db, $log;

    foreach (['movies', 'shows'] as $media_type) {
        $query = $db->query('SELECT id,path FROM library_' . $media_type . ' WHERE file_hash IS \'\' LIMIT 200');
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
    } else {
        $paths[] = $cfg['MOVIES_PATH'];
    }

    if (is_array($cfg['SHOWS_PATH'])) {
        $paths = array_merge($paths, $cfg['SHOWS_PATH']);
    } else {
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
    if (valid_array($shows)) {
        foreach ($shows as $show) {
            mediadb_getSeasons($show['themoviedb_id']);
            $where['themoviedb_id'] = ['value' => $show['themoviedb_id']];
            $db->update('shows_details', $update, $where);
            $log->debug("Update seasons on {$show['themoviedb_id']}");
            $i++;
        }
    }
    $log->info("Seasons updated: $i");
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

    //check if any master have no childs and childs pointing to a non exists master

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
                $log->warning('Detected master without childs, removing master id: ' . $master['id']);
                $db->deleteItemById($library_master, $master['id']);
            }
        }

        foreach ($childs as $child) {
            $delete_child = 0;
            if (empty($child['master']) && !empty($child['themoviedb_id'])) {
                $log->warning('Detected identified child with empty master, deleting for reidentify: ' . $child['id']);
                $delete_child = 1;
            }
            if (!empty($child['master']) && !empty($child['themoviedb_id'])) {
                if (array_search($child['master'], array_column($masters, 'id')) === false) {
                    $log->warning(' Detected an identify child with a missing master (' . $child['master'] . '), delete for reidentify tmdbid: ' . $child['id']);
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
    foreach (['movies', 'shows'] as $media_type) {
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
                $item_cached = mediadb_getFromCache($media_type, $item['themoviedb_id']);
                if (!valid_array($item_cached)) {
                    continue;
                }
                $update['updated'] = time();
                $update['plot'] = $item_cached['plot'];
                $update['rating'] = $item_cached['rating'];
                $update['popularity'] = isset($item_cached['popularity']) ? $item_cached['popularity'] : 0;
                $update['poster'] = !empty($item_cached['poster']) ? $item_cached['poster'] : null;
                $update['release'] = $item_cached['release'];
                $update['genre'] = !empty($item_cached['genre']) ? $item_cached['genre'] : null;
                !empty($item_cached['trailer']) ? $update['trailer'] = $item_cached['trailer'] : $update['trailer'] = '';
                $media_type == 'shows' ? $update['ended'] = $item_cached['ended'] : null;

                $db->update('library_master_' . $media_type, $update, ['id' => ['value' => $item['id']]]);
            }
        }
    }
}

/*
  1º check movies/shows with empty trailer and update if we already not update in DB_UPD_MISSING_DELAY
  2º check no empty trailers movie/shows and update trailer link after DB_UPD_LONG_DELAY
 */
/*
 * Since we clean tmdb cache and update masters we don't need this


  function update_trailers() {
  global $db, $cfg, $log;

  $log->debug('Executing update trailers');
  $limit = 15;

  //IMPROVE: This can be done in one query per table
  // Update missing trailers and never try get trailer
  foreach (['tmdb_search_movies', 'tmdb_search_shows'] as $table) {
  $update = [];

  if ($table == 'library_movies' || $table == 'tmdb_search_movies') {
  $media_type = 'movies';
  } else {
  $media_type = 'shows';
  }

  $update['updated'] = $time_now = time();
  $next_update = time() - $cfg['db_upd_missing_delay'];

  $query = "SELECT DISTINCT themoviedb_id FROM $table WHERE trailer = '' OR  (trailer = '0' AND updated < $next_update) LIMIT $limit";
  $result = $db->query($query);

  $results = $db->fetchAll($result);

  foreach ($results as $item) {
  $trailer = mediadb_getTrailer($media_type, $item['themoviedb_id']);
  if (!empty($trailer)) {
  if (substr(trim($trailer), 0, 5) != 'https') {
  $trailer = str_replace('http', 'https', $trailer);
  }
  $update['trailer'] = $trailer;
  $log->info("(1) Update $table trailer on tmdb_id {$item['themoviedb_id']} trailer $trailer");
  } else {
  $update['trailer'] = 0;
  }
  $where_upd = ['themoviedb_id' => ['value' => $item['themoviedb_id']]];

  $db->update($table, $update, $where_upd);
  }

  // LONG DELAY UPDATE TRAILER
  $update = [];
  $update['updated'] = $time_now = time();
  $next_update = time() - $cfg['db_upd_long_delay'];
  $query = "SELECT DISTINCT themoviedb_id FROM $table WHERE trailer IS NOT NULL AND updated < $next_update LIMIT $limit";
  $result = $db->query($query);
  $results = $db->fetchAll($result);

  foreach ($results as $item) {
  $trailer = mediadb_getTrailer($media_type, $item['themoviedb_id']);
  if (!empty($trailer)) {
  if (substr(trim($trailer), 0, 5) != 'https') {
  $trailer = str_replace('http', 'https', $trailer);
  }
  $update['trailer'] = $trailer;
  $log->info("(2) Update $table trailer on tmdb_id {$item['themoviedb_id']} trailer $trailer");
  } else {
  $update['trailer'] = 0;
  }
  $where_upd = ['themoviedb_id' => ['value' => $item['themoviedb_id']]];

  $db->update($table, $update, $where_upd);
  }
  }
  }
 */
