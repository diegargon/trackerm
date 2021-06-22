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

    $log->info("Rebuild $media_type called");
    $items = [];
    $files = find_media_files($path, $cfg['media_ext']);

    /* Avoid broken links && Detect Dups  */
    linked_files_check($files);

    $library_table = 'library_' . $media_type;

    $media = $db->getTableData($library_table);
    $i = 0;

    //Check if each media path it in $files if not probably delete or moved and must clean.
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
        /*
         * If is a show we check if already have other episodes identified to identify this.
         * true: identify and &unset id
         */

        if ($media_type == 'shows' && valid_array($media)) {
            ident_by_already_have_show($media, $insert_ids);
        }
        /*
         * We check library history for auto identify in we had that file.
         * true: identify and &unset id
         */
        valid_array($insert_ids) ? ident_by_history($media_type, $insert_ids) : null;
        /*
         * Last if auto_identify is on we check title agains online db.
         */
        if (!empty($cfg['auto_identify'])) {
            (valid_array($insert_ids)) ? auto_ident_exact($media_type, $insert_ids) : null;
        }
    }

    return true;
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
                    $new_total_size = $master['total_size'] - $item['size'];
                    $new_total_items = $master['total_items'] - 1;
                    $db->updateItemById('library_master_' . $media_type, $master['id'], ['total_size' => $new_total_size, 'total_items' => $new_total_items]);
                } else {
                    $db->deleteItemById('library_master_' . $media_type, $master['id']);
                }
            }

            $db->deleteItemById('library_' . $media_type, $item['id']);
        }
    }
}

function linked_files_check(array &$files) {
    global $log;

    $realpaths = [];
    foreach ($files as $file_key => $file) {
        if (is_link($file) && !file_exists($file)) {
            $log->info('Broken link detected ignoring (cli mode will clean)...' . $file);
            unset($files[$file_key]);
        }
        if (is_link($file) && file_exists($file)) {
            if (array_key_exists(realpath($file), $realpaths)) {
                $log->info('Duplicate link detected <br/>' . $file . "<br/>" . $realpaths[realpath($file)]);
                $link1 = lstat($realpaths[realpath($file)]);
                $link2 = lstat($file);
                /* Remove and unset old */
                if ($link1['ctime'] < $link2['ctime']) {
                    if (unlink($realpaths[realpath($file)])) {
                        $log->info('Cleaning duplicate link success: ' . $realpaths[realpath($file)]);
                        foreach ($files as $_file_key => $_file) {
                            if ($_file === $realpaths[realpath($file)]) {
                                unset($files[$_file_key]);
                            }
                        }
                    }
                } else {
                    if (unlink($file)) {
                        $log->info('Cleaning duplicate link success: ' . $file);
                        foreach ($files as $_file_key => $_file) {
                            if ($_file === $file) {
                                unset($files[$_file_key]);
                            }
                        }
                    }
                }
            } else {
                $realpaths[realpath($file)] = $file;
            }
        }
    }
}

function update_master_stats() {
    global $db, $log;

    foreach (['movies', 'shows'] as $media_type) {
        $masters_media = $db->getTableData('library_master_' . $media_type);
        $childs_media = $db->getTableData('library_' . $media_type);

        if (valid_array($masters_media)) {
            foreach ($masters_media as $master_media) {
                $total_size = 0;
                $set = [];
                $items = [];

                foreach ($childs_media as $child_media) {
                    if (!empty($child_media['master']) && $child_media['master'] == $master_media['id']) {
                        $items[] = $child_media;
                    }
                }

                if (valid_array($items)) {
                    $num_items = count($items);
                    foreach ($items as $item) {
                        $total_size = $total_size + $item['size'];
                    }

                    if ($master_media['total_size'] != $total_size) {
                        $log->debug("Discrepancy on master size {$master_media['title']} $total_size:{$master_media['total_size']}");
                        $set['total_size'] = $total_size;
                    }
                    if ($master_media['total_items'] != $num_items) {
                        $log->debug("Discrepancy on master items {$master_media['title']} $num_items:{$master_media['total_items']}");
                        $set['total_items'] = $num_items;
                    }
                    if (valid_array($set)) {
                        $db->update('library_master_' . $media_type, $set, ['id' => ['value' => $master_media['id']]]);
                    }
                }
            }
        }
    }
}

function get_have_shows($oid) {
    global $db;

    if (!is_numeric($oid)) {
        return false;
    }

    $master = $db->getItemByField('library_master_shows', 'themoviedb_id', $oid);
    if (!valid_array($master)) {
        return false;
    }

    $where['master'] = ['value' => $master['id']];
    $results = $db->select('library_shows', null, $where);
    $shows = $db->fetchAll($results);

    return valid_array($shows) ? $shows : false;
}

function get_have_shows_season($oid, $season) {
    global $db;

    if (!is_numeric($oid) || !is_numeric($season)) {
        return false;
    }
    $master = $db->getItemByField('library_master_shows', 'themoviedb_id', $oid);
    if (!valid_array($master)) {
        return false;
    }

    $where['master'] = ['value' => $master['id']];
    $where['season'] = ['value' => $season];
    $results = $db->select('library_shows', null, $where);
    $shows = $db->fetchAll($results);

    return valid_array($shows) ? $shows : false;
}
