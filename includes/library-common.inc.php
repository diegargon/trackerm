<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function rebuild($media_type, $paths) {
    global $log, $prefs;
    //r_blocker prevent forever locks, if more than 3 consecutive locks (probably get stuck, resetting)
    if (($r_blocker = $prefs->getPrefsItem('rebuild_blocker', true)) && $r_blocker <= 3) {
        $prefs->setPrefsItem('rebuild_blocker', ++$r_blocker, true);
        $log->warning("Rebuild: blocked ($r_blocker)");
        return false;
    }
    /* Block for avoid cli & user rebuild same time */
    $prefs->setPrefsItem('rebuild_blocker', 1, true);

    if (valid_array($paths)) {
        foreach ($paths as $path) {
            rebuild_media($media_type, $path, $paths[0]);
            sleep(1);
        }
    } else {
        rebuild_media($media_type, $paths, $paths);
    }
    $prefs->setPrefsItem('rebuild_blocker', 0, true);
}

function rebuild_media($media_type, $path, $links_path) {
    global $cfg, $db, $log, $LNG;

    $log->info("Rebuild $media_type called ($path)");
    $items = [];
    $files = find_media_files($path, $cfg['media_ext']);

    /* Avoid broken links && Detect Dups  */
    linked_files_check($files, $links_path);

    $library_table = 'library_' . $media_type;

    $media_db = $db->getTableData($library_table);
    $i = 0;

    //Check if each media path it in $files if not probably delete or moved and must clean.
    (valid_array($media_db)) ? clean_database($media_type, $path, $files, $media_db) : null;

    foreach ($files as $file) {
        if (!valid_array($media_db) ||
                array_search($file, array_column($media_db, 'path')) === false
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
            /* Todo: Since use of master we not need some fields */
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
                    $log->addStatusMsg($msg_log);
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

        if ($media_type == 'shows' && valid_array($media_db)) {
            ident_by_already_have_show($media_db, $insert_ids);
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
            (valid_array($insert_ids)) ? auto_ident($media_type, $insert_ids) : null;
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
            $stats['movies_paths'][$movies_path]['path'] = $movies_path;
            $stats['movies_paths'][$movies_path]['free'] = $movies_free_space;
            $stats['movies_paths'][$movies_path]['total'] = $movies_total_space;
            $stats['movies_paths'][$movies_path]['basename'] = $movies_path_name;
        }
    } else {
        $stats['movies_paths'][$movies_path]['path'] = $movies_path;
        $stats['movies_paths'][$cfg['MOVIES_PATH']]['free'] = human_filesize(disk_free_space($cfg['MOVIES_PATH']));
        $stats['movies_paths'][$cfg['MOVIES_PATH']]['total'] = human_filesize(disk_total_space($cfg['MOVIES_PATH']));
        $stats['movies_paths'][$cfg['MOVIES_PATH']]['basename'] = basename($cfg['MOVIES_PATH']);
    }

    if (is_array($cfg['SHOWS_PATH'])) {
        foreach ($cfg['SHOWS_PATH'] as $shows_path) {
            $shows_path_name = basename($shows_path);
            $shows_free_space = human_filesize(disk_free_space($shows_path));
            $shows_total_space = human_filesize(disk_total_space($shows_path));
            $stats['shows_paths'][$shows_path]['path'] = $shows_path;
            $stats['shows_paths'][$shows_path]['free'] = $shows_free_space;
            $stats['shows_paths'][$shows_path]['total'] = $shows_total_space;
            $stats['shows_paths'][$shows_path]['basename'] = $shows_path_name;
        }
    } else {
        $stats['shows_paths'][$shows_path]['path'] = $shows_path;
        $stats['movies_paths'][$cfg['SHOWS_PATH']]['free'] = human_filesize(disk_free_space($cfg['SHOWS_PATH']));
        $stats['movies_paths'][$cfg['SHOWS_PATH']]['total'] = human_filesize(disk_total_space($cfg['SHOWS_PATH']));
        $stats['movies_paths'][$cfg['SHOWS_PATH']]['basename'] = basename($cfg['SHOWS_PATH']);
    }

    $stats['db_size'] = file_exists($cfg['DB_FILE']) ? human_filesize(filesize($cfg['DB_FILE'])) : 0;

    return $stats;
}

/*
 * Check database against files for deleted files. Exec before rebuild
 * if files was remove keep a library_history entry for reidentify with file_hash and delete the entry
 */

function clean_database($media_type, $path, $files, $media) {
    global $log, $db, $LNG;

    
    foreach ($media as $item) {
        $path_len = mb_strlen($path);
        
        if ($path[$path_len - 1] != '/') {
            // Assure that path end is / to not match ../Movies and /Movies2
            $path .= '/';
        }
        if ((substr($item['path'], 0, mb_strlen($path)) != $path)) {
            /*
             * Assure the item  path is set in the check path since this function called for each path
             * and the files database contain all path files and not want remove if is in another path.
             * 
             */
            continue;
        }
        /* File be known in $path check and remove if not exists in $files  */

        if (!in_array($item['path'], $files)) {

            $master = [];

            $log->addStatusMsg('[' . $LNG['L_NOTE'] . '] ' . basename($item['path']) . ' ' . $LNG['L_NOTE_MOVDEL']);

            if (!empty($item['master'])) {
                $master = $db->getItemById('library_master_' . $media_type, $item['master']);
            }

            if (valid_array($master) && !empty($item['file_hash'])) {
                $values['title'] = $master['title'];
                $values['themoviedb_id'] = $master['themoviedb_id'];
                $values['clean_title'] = clean_title($item['title']);
                $values['media_type'] = $media_type;
                $values['file_name'] = $item['file_name'];
                $values['custom_poster'] = $master['custom_poster'];
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

/*
 * Search and clean broken links
 */

function linked_files_check(array &$files, $links_path) {
    global $log;

    $realpaths = [];
    foreach ($files as $file_key => $file) {
        if(!is_link($file)) {
            continue;
        }
        //Links Broken
        if (!file_exists($file)) {
            if (defined('IN_CLI')) {
                $log->info('Broken link detected removing...' . $file);
                unlink($file);
            } else {
                $log->info('Broken link detected ignoring (cli mode will clean)...' . $file);
            }
            unset($files[$file_key]);
            continue;
            
        }

        if ($links_path[mb_strlen($links_path) - 1] != '/') {
            // Assure that path end is / to not match ../M+ovies and /Movies2
            $links_path .= '/';
        }
        
        if ((substr($file, 0, mb_strlen($links_path)) != $links_path)) {        
            //Link only allowed on path[0] this links is not there remove
            if (defined('IN_CLI')) {
                $log->info('Removing link in not allowed path, (perhaps you change path[0]?...' . $file);                             
                unlink($file);
            } else {
                $log->info('Detected link in not allowed path cli will be removing it, (perhaps you change path[0]?...' . $file);
                
            }
            unset($files[$file_key]);
            continue;            
        } 
        
        /* Remove duplicate links: can occur when changing naming config */
        /* FIXME El codigo es lioso, al guarda como key la ruta real y en contenido la ruta del enlace (else final)
         * En la linea unlink($realpaths[realpath($file)] haciendo referencia al enlace con el realpath del archivo
         */
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

/*
 * Check integrity of items,size of master
 */

function check_master_stats() {
    global $db, $log;

    $log->debug('Check master stats');
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

/*
 * Update total_size, total_items on masters
 * Values use in index stats
 */

function update_library_stats() {
    global $db, $log;

    $log->debug('Updating library stats');
    $movies_size = 0;
    $shows_size = 0;

    $results = $db->query('SELECT total_size FROM library_master_movies');
    $movies_db = $db->fetchAll($results);
    $num_movies = count($movies_db);

    if (valid_array($movies_db)) {
        foreach ($movies_db as $db_movie) {
            if (isset($db_movie['total_size'])) {
                $movies_size = $movies_size + $db_movie['total_size'];
            }
        }
        $movies_size = human_filesize($movies_size);
    }
    $results = $db->query('SELECT total_size,total_items FROM library_master_shows');
    $shows_db = $db->fetchAll($results);
    $num_shows = count($shows_db);
    $num_episodes = 0;

    if (valid_array($shows_db)) {
        foreach ($shows_db as $db_show) {
            if (isset($db_show['total_size'])) {
                $shows_size = $shows_size + $db_show['total_size'];
            }
            $num_episodes = $num_episodes + $db_show['total_items'];
        }
        $shows_size = human_filesize($shows_size);
    }

    $db->query("UPDATE config SET cfg_value='$num_movies' WHERE cfg_key='stats_movies' LIMIT 1");
    $db->query("UPDATE config SET cfg_value='$num_shows' WHERE cfg_key='stats_shows' LIMIT 1");
    $db->query("UPDATE config SET cfg_value='$num_episodes' WHERE cfg_key='stats_shows_episodes' LIMIT 1");
    $db->query("UPDATE config SET cfg_value='$movies_size' WHERE cfg_key='stats_total_movies_size' LIMIT 1");
    $db->query("UPDATE config SET cfg_value='$shows_size' WHERE cfg_key='stats_total_shows_size' LIMIT 1");
}

/*
 * Remove register master & childs
 */

function delete_register(int $master_id, string $media_type) {
    global $db;

    $library = 'library_' . $media_type;
    $library_master = 'library_master_' . $media_type;

    $db->deleteItemById($library_master, $master_id);
    $db->deleteItemsByField($library, 'master', $master_id);

    return true;
}

/*
 * Remove a file, master if empty and parent directory if empty and match the title
 *
 */

function delete_file(int $file_id, int $master_id, string $media_type) {
    global $db, $cfg, $log, $LNG;

    $library = 'library_' . $media_type;
    $library_master = 'library_master_' . $media_type;
    $file_item = $db->getItemById($library, $file_id);
    $master = $db->getItemById($library_master, $master_id);
    $return = true;
    $media_type == 'movies' ? $root_dirs = $cfg['MOVIES_PATH'] : $root_dirs = $cfg['SHOWS_PATH'];
    $dirname = dirname($file_item['path']);

    if (!valid_array($file_item) || !valid_array($master)) {
        return false;
    }

    if (is_link($file_item['path'])) {
        $cfg['msg_warn'] = $LNG['L_FILE_ISLINK_IGNORING'];
        return false;
    }

    //For be sure nothing very wrong in the file path we check against library paths
    $good_path = 0;
    foreach ($root_dirs as $root_dir) {
        if (stripos($file_item['path'], $root_dir) !== false) {
            $good_path = 1;
            break;
        }
    }

    if (!$good_path) {
        $log->err('Wrong path in file library id:' . $file_item['path']);
        return false;
    }
    //Remove file and insert history
    if (is_writable($file_item['path'])) {
        $log->debug("Removing {$file_item['path']}");
        unlink($file_item['path']);
        //Update history.

        $values['title'] = $master['title'];
        $values['themoviedb_id'] = $master['themoviedb_id'];
        $values['clean_title'] = clean_title($file_item['title']);
        $values['media_type'] = $media_type;
        $values['file_name'] = $file_item['file_name'];
        $values['custom_poster'] = $master['custom_poster'];
        $values['file_hash'] = $file_item['file_hash'];
        isset($file_item['season']) ? $values['season'] = $file_item['season'] : null;
        isset($file_item['episode']) ? $values['episode'] = $file_item['episode'] : null;
        $item_hist_id = $db->getIdByField('library_history', 'file_hash', $file_item['file_hash']);
        if (!$item_hist_id) {
            $db->insert('library_history', $values);
        } else {
            $db->update('library_history', $values, ['id' => ['value' => $item_hist_id]], 'LIMIT 1');
        }
    } else {
        $cfg['msg_warn'] = $LNG['L_ERR_FILE_PERMS'];
        $log->addStatusMsg($LNG['L_ERR_FILE_PERMS'] . ': ' . $file_item['path']);
        $log->err($LNG['L_ERR_FILE_PERMS'] . ': ' . $file_item['path']);
        return false;
    }

    //Remove Registers
    $db->deleteItemById($library, $file_id);
    $log->addStatusMsg($LNG['L_DELETE_ENTRY_MANUALLY'] . ': ' . $file_item['file_name']);
    if ($master['total_items'] == 1) {
        $db->deleteItemById($library_master, $master_id);
        $return = 'SUCCESS_NOMASTER';
        $log->addStatusMsg($LNG['L_DELETE_ENTRY_MANUALLY'] . ': (Master) ' . $master['title']);
    } else {
        $new_size = $master['total_size'] - $file_item['size'];
        $db->updateItemById($library_master, $master['id'], ['total_items' => $master['total_items'] - 1, 'total_size' => $new_size]);
    }

    //If no more media files remove dirs
    if (delete_empty_media_dirs($dirname, $media_type)) {
        //Delete empty ok shows need another down dir since /show name/Season 1
        if ($media_type == 'shows') {
            $show_dirname = dirname($dirname);
            delete_empty_media_dirs($show_dirname, $media_type);
        }
    }

    return $return;
}

/*
 * Remove master->child files, and parent directory if empty and match the title
 */

function delete_files(int $master_id, string $media_type) {
    global $db;

    $library = 'library_' . $media_type;

    $return = false;

    $items = $db->getItemsByField($library, 'master', $master_id);

    if (valid_array($items)) {
        foreach ($items as $item) {
            $return = delete_file($item['id'], $master_id, $media_type);
        }
    }

    return $return;
}

/*
 * Delete the parent dir if not exists media files in, will clean all other files.
 */

function delete_empty_media_dirs(string $dirname, string $media_type) {
    global $cfg, $log;

    $media_type == 'movies' ? $root_dirs = $cfg['MOVIES_PATH'] : $root_dirs = $cfg['SHOWS_PATH'];
    clearstatcache();
    if (count(find_media_files($dirname, $cfg['media_ext'])) == 0) {
        $files_in_dir = [];
        get_dir_contents($dirname, $files_in_dir);
        foreach ($files_in_dir as $file_in_dir) {
            if (!is_dir($file_in_dir) && is_writable($file_in_dir)) {
                if (!in_array(get_file_ext($file_in_dir), $cfg['media_ext'])) {
                    $log->debug("unlink: " . $file_in_dir . "<br>");
                    unlink($file_in_dir);
                }
            } else {
                $log->debug("Found file not writable $file_in_dir, cant remove directory");
                return false;
            }
        }

        clearstatcache();
        $files_in_dir = [];
        if (count(get_dir_contents($dirname, $files_in_dir)) == 0) {
            $found = 0;
            //Make sure is not the root path;
            foreach ($root_dirs as $root_dir) {
                if ($dirname == $root_dir) {
                    $found = 1;
                    break;
                }
            }
            if (!$found && is_writable($dirname)) {
                $log->debug("Removing directory $dirname");
                rmdir($dirname);
            } else {
                $log->debug("Found something in $dirname that not going to remove leaving...<br>");
                return false;
            }
        } else {
            $files_in_dir = [];
            $log->debug("Found files in -> $dirname  omit removing directory " . count(get_dir_contents($dirname, $files_in_dir)));
            return false;
        }
    }

    return true;
}

function get_poster($item) {
    global $cfg;

    $poster = $cfg['img_url'] . '/not_available.jpg';

    if ($cfg['cache_images']) {
        if (!empty($item['custom_poster'])) {
            $cache_img_response = cache_img($item['custom_poster']);
            if ($cache_img_response !== false) {
                $poster = $cache_img_response;
            } else {
                $poster = $item['custom_poster'];
            }
        } else if (!empty($item['poster'])) {
            $cache_img_response = cache_img($item['poster']);
            if ($cache_img_response !== false) {
                $poster = $cache_img_response;
            } else {
                if (Filter::varImgUrl($item['poster'])) {
                    $poster = $item['poster'];
                }
            }
        } else if (!empty($item['guessed_poster']) && $item['guessed_poster'] != -1) {
            $cache_img_response = cache_img($item['guessed_poster']);
            if ($cache_img_response !== false) {
                $poster = $cache_img_response;
            } else {
                $poster = $item['guessed_poster'];
            }
        }
    } else {
        if (!empty($item['custom_poster'])) {
            $poster = $item['custom_poster'];
        } else if (!empty($item['poster'])) {
            $poster = $item['poster'];
        } else if (!empty($item['guessed_poster']) && $item['guessed_poster'] != -1) {
            $poster = $item['guessed_poster'];
        }
    }

    return $poster;
}
