<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function rebuild($media_type, $path) {
    if (getPrefsItem('rebuild_blocker', true)) {
        return false;
    }
    setPrefsItem('rebuild_blocker', 1, true);

    if (valid_array($path)) {
        foreach ($path as $item) {
            _rebuild($media_type, $item);
            sleep(1);
        }
    } else {
        _rebuild($media_type, $path);
    }

    setPrefsItem('rebuild_blocker', 0, true);
}

function _rebuild($media_type, $path) {
    global $cfg, $db, $log, $LNG;

    $log->debug("Rebuild $media_type called");

    $items = [];
    $files = findMediaFiles($path, $cfg['media_ext']);

    if ($media_type == 'movies') {
        $library_table = 'library_movies';
        $ilink = 'movies_library';
    } else if ($media_type == 'shows') {
        $library_table = 'library_shows';
        $ilink = 'shows_library';
    }

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
                'ilink' => $ilink,
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

            // auto identify episodes froms shows already identified
            if ($media_type == 'shows') {
                foreach ($media as $id_item) {

                    if ($id_item['predictible_title'] === ucwords($predictible_title) &&
                            !empty($id_item['themoviedb_id'])
                    ) {
                        $items[$i]['themoviedb_id'] = $id_item['themoviedb_id'];
                        $items[$i]['title'] = $id_item['title'];
                        $items[$i]['clean_title'] = clean_title($id_item['title']);
                        $items[$i]['poster'] = $id_item['poster'];
                        $items[$i]['rating'] = $id_item['rating'];
                        $items[$i]['popularity'] = $id_item['popularity'];
                        $items[$i]['scene'] = $id_item['scene'];
                        $items[$i]['lang'] = $id_item['lang'];
                        $items[$i]['plot'] = $id_item['plot'];
                        isset($id_item['trailer']) ? $items[$i]['trailer'] = $id_item['trailer'] : null;
                        $items[$i]['original_title'] = $id_item['original_title'];
                    }
                }
            }
        }
        $i++;
    }
    if (isset($items)) {
        $insert_ids = $db->addItems($library_table, $items);
        if (valid_array($insert_ids)) {
            $insert_ids = check_history($media_type, $insert_ids);
            if (!empty($cfg['auto_identify'])) {
                (!empty($insert_ids) && count($insert_ids) > 0 ) ? auto_ident_exact($media_type, $insert_ids) : null;
            }
        }
    }

    return true;
}

function show_identify_media($media_type) {
    global $LNG, $cfg, $db, $filter;

    $titles = '';
    $i = 0;
    $uniq_shows = [];

    $iurl = '?page=' . $filter->getString('page');

    $result = $db->query("SELECT * FROM library_$media_type WHERE title = '' OR themoviedb_id = ''");
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
        //Need requery for failed automate ident
        $result = $db->query("SELECT * FROM library_$media_type WHERE title = '' OR themoviedb_id = ''");
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
                //var_dump($item);
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
            $titles .= $table = getTpl('identify_item', array_merge($LNG, $item, $title_tdata));
            $i++;
        }
    }

    if (!empty($titles)) {
        $tdata['titles'] = $titles;
        $tdata['head'] = $LNG['L_IDENT_' . strtoupper($media_type) . ''];

        $table = getTpl('identify', array_merge($LNG, $tdata));

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
        $db_item = $db->getItemById('library_' . $media_type, $id);
        if ($media_type == 'movies') {
            $search_media = mediadb_searchMovies($db_item['predictible_title']);
        } else if ($media_type == 'shows') {
            if ((array_search($db_item['predictible_title'], $uniq_shows)) === false) {
                $search_media = mediadb_searchShows($db_item['predictible_title']);
                $uniq_shows[] = $db_item['predictible_title'];
            } else {
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
    if (!valid_array($id_pairs)) {
        return false;
    }

    foreach ($id_pairs as $my_id => $tmdb_id) {
        (!empty($my_id) && !emptY($tmdb_id)) ? ident_by_id($media_type, $tmdb_id, $my_id) : null;
    }
}

function ident_by_id($media_type, $tmdb_id, $id) {
    $db_data = mediadb_getFromCache($media_type, $tmdb_id);
    ($db_data) ? submit_ident($media_type, $db_data, $id) : null;
}

function submit_ident($media_type, $item_data, $id) {
    global $db, $log;


    $q_results = $db->select('library_' . $media_type, '*', ['themoviedb_id' => ['value' => $item_data['themoviedb_id']]], 'LIMIT 1');
    $dup_result = $db->fetchAll($q_results);
    if (valid_array($dup_result)) {
        $log->debug('Discarding item duplicate ' . $item_data['title']);
        return false;
    }
    if (!empty($item_data['title'])) {
        $update_fields['title'] = $item_data['title'];
        $update_fields['clean_title'] = clean_title($item_data['title']);
    }
    if (!empty($item_data['name'])) {
        $update_fields['name'] = $item_data['name'];
        $update_fields['clean_title'] = clean_title($item_data['name']);
    }
    $update_fields['themoviedb_id'] = $item_data['themoviedb_id'];
    !empty($item_data['poster']) ? $update_fields['poster'] = $item_data['poster'] : null;
    !empty($item_data['original_title']) ? $update_fields['original_title'] = $item_data['original_title'] : null;
    !empty($item_data['rating']) ? $update_fields['rating'] = $item_data['rating'] : null;
    !empty($item_data['popularity']) ? $update_fields['popularity'] = $item_data['popularity'] : null;
    !empty($item_data['scene']) ? $update_fields['scene'] = $item_data['scene'] : null;
    !empty($item_data['lang']) ? $update_fields['lang'] = $item_data['lang'] : null;
    !empty($item_data['trailer']) ? $update_fields['trailer'] = $item_data['trailer'] : null;
    !empty($item_data['plot']) ? $update_fields['plot'] = $item_data['plot'] : null;
    !empty($item_data['release']) ? $update_fields['release'] = $item_data['release'] : null;

    //TODO CHECK IF EXIST themovie_id  since is UNIQUE
    if ($media_type == 'movies') {
        $db->updateItemById('library_movies', $id, $update_fields);
    } else if ($media_type == 'shows') {
        $mylib_shows = $db->getItemById('library_shows', $id);
        $where['predictible_title'] = ['value' => $mylib_shows['predictible_title']];
        $db->update('library_shows', $update_fields, $where);
    }
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
            if (!empty($item_history) && !empty($item_history['themoviedb_id'])) {
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

    foreach ($media as $item) {
        if (!in_array($item['path'], $files)) {
            $log->addStateMsg('[' . $LNG['L_NOTE'] . '] ' . $item['title'] . ' ' . $LNG['L_NOTE_MOVDEL']);
            if (isset($item['themoviedb_id'])) {
                $values['title'] = $item['title'];
                $values['themoviedb_id'] = $item['themoviedb_id'];
                $values['clean_title'] = clean_title($item['title']);
                $values['media_type'] = $media_type;
                $values['file_name'] = $item['file_name'];
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
            if ($media_type == 'movies') {
                $db->deleteItemById('library_movies', $item['id']);
            } else if ($media_type == 'shows') {
                $db->deleteItemById('library_shows', $item['id']);
            }
        }
    }
}
