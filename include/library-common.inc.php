<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function rebuild($media_type, $path) {
    global $cfg, $db, $log;

    $items = [];
    $files = findfiles($path, $cfg['media_ext']);

    if ($media_type == 'movies') {
        $library_table = 'library_movies';
        $ilink = 'movies_library';
    } else if ($media_type == 'shows') {
        $library_table = 'library_shows';
        $ilink = 'shows_library';
    }

    $media = $db->getTableData($library_table);

    $i = 0;

    //check if each media path it in $files if not proably delete, then delete db entry
    //this avoid problems if the file was moved
    (isset($media) && count($media) > 0) ? clean_database($media_type, $files, $media) : null;

    foreach ($files as $file) {
        if ($media === false ||
                array_search($file, array_column($media, 'path')) === false
        ) {


            $file_name = trim(basename($file));
            $predictible_title = getFileTitle($file_name);
            $year = getFileYear($file_name);
            $tags = getFileTags($file_name);
            $ext = substr($file_name, -3);
            $hash = file_hash($file);

            $items[$i] = [
                'ilink' => $ilink,
                'file_name' => $file_name,
                'size' => filesize($file),
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
                    $msg_log = 'Can\'t determine SE for this file: ' . $items[$i]['file_name'];
                    $log->addStateMsg($msg_log);
                    $log->warning($msg_log);
                    $items[$i]['season'] = 'X';
                    $items[$i]['episode'] = 'X';
                }
            }

            // auto identify episodes already identified
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
        if (!empty($insert_ids) && (count($insert_ids) > 0)) {
            check_history($media_type, $insert_ids);
        }
    }

    return true;
}

function clean_database($media_type, $files, $media) {
    global $log, $db;

    foreach ($media as $item) {
        if (!in_array($item['path'], $files)) {
            $log->addStateMsg('Media ' . $item['title'] . ' seems moved or deleted removing from db');
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
                    $db->update('library_history', $item_hist_id, $values);
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

function identify_media($media_type, $media) {
    global $LNG, $cfg;

    $titles = '';
    $i = 0;
    $uniq_shows = [];

    $iurl = '?page=' . $_GET['page'];

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

            if (!empty($db_media)) {

                foreach ($db_media as $db_item) {
                    $year = trim(substr($db_item['release'], 0, 4));
                    $title_tdata['results_opt'] .= '<option value="' . $db_item['id'] . '">';
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

function submit_ident($type, $items) {
    global $db;

    foreach ($items as $my_id => $db_id) {
        if (!empty($db_id)) {
            $db_item = mediadb_getByLocalId($db_id);
            if (!empty($db_item['title'])) {
                $update_fields['title'] = $db_item['title'];
                $update_fields['clean_title'] = clean_title($db_item['title']);
            }
            if (!empty($db_item['name'])) {
                $update_fields['name'] = $db_item['name'];
                $update_fields['clean_title'] = clean_title($db_item['name']);
            }
            $update_fields['themoviedb_id'] = $db_item['themoviedb_id'];
            !empty($db_item['poster']) ? $update_fields['poster'] = $db_item['poster'] : null;
            !empty($db_item['original_title']) ? $update_fields['original_title'] = $db_item['original_title'] : null;
            !empty($db_item['rating']) ? $update_fields['rating'] = $db_item['rating'] : null;
            !empty($db_item['popularity']) ? $update_fields['popularity'] = $db_item['popularity'] : null;
            !empty($db_item['scene']) ? $update_fields['scene'] = $db_item['scene'] : null;
            !empty($db_item['lang']) ? $update_fields['lang'] = $db_item['lang'] : null;
            !empty($db_item['trailer']) ? $update_fields['trailer'] = $db_item['trailer'] : null;
            !empty($db_item['plot']) ? $update_fields['plot'] = $db_item['plot'] : null;
            !empty($db_item['release']) ? $update_fields['release'] = $db_item['release'] : null;

            if ($type == 'movies') {
                $db->updateItemById('library_movies', $my_id, $update_fields);
            } else if ($type == 'shows') {
                $mylib_show = $db->getItemById('library_shows', $my_id);
                $update_fields['predictible_title'] = $mylib_show['predictible_title'];
                $db->updateItemsByField('library_shows', $update_fields, 'predictible_title');
            }
        }
    }
}

function check_history($media_type, $ids) {
    global $db;

    if ($media_type == 'movies') {
        $library = 'library_movies';
    } else {
        $library = 'library_shows';
    }
    foreach ($ids as $id) {
        if (!is_numeric($id)) {
            continue;
        }
        $item = $db->getItemById($library, $id);

        if (empty($item['themoviedb_id'])) {
            $where = [
                'media_type' => ['value' => $media_type],
                'file_hash' => ['value' => $item['file_hash']],
            ];

            $results = $db->select('library_history', 'themoviedb_id', $where, 'LIMIT 1');
            $item_history = $db->fetch($results);
            $db->finalize($results);
            if (!empty($item_history) && !empty($item_history['themoviedb_id'])) {

                auto_ident($media_type, $item_history['themoviedb_id'], $id);
            }
        }
    }
}

function auto_ident($media_type, $online_db_id, $id) {
    global $db;

    if ($media_type == 'movies') {
        $library = 'library_movies';
    } else {
        $library = 'library_shows';
    }

    $db_item = mediadb_getByDbId($media_type, $online_db_id);

    if (!empty($db_item['title'])) {
        $update_fields['title'] = $db_item['title'];
        $update_fields['clean_title'] = clean_title($db_item['title']);
    }
    if (!empty($db_item['name'])) {
        $update_fields['name'] = $db_item['name'];
        $update_fields['clean_title'] = clean_title($db_item['name']);
    }
    $update_fields['themoviedb_id'] = $db_item['themoviedb_id'];
    !empty($db_item['poster']) ? $update_fields['poster'] = $db_item['poster'] : null;
    !empty($db_item['original_title']) ? $update_fields['original_title'] = $db_item['original_title'] : null;
    !empty($db_item['rating']) ? $update_fields['rating'] = $db_item['rating'] : null;
    !empty($db_item['popularity']) ? $update_fields['popularity'] = $db_item['popularity'] : null;
    !empty($db_item['scene']) ? $update_fields['scene'] = $db_item['scene'] : null;
    !empty($db_item['lang']) ? $update_fields['lang'] = $db_item['lang'] : null;
    !empty($db_item['trailer']) ? $update_fields['trailer'] = $db_item['trailer'] : null;
    !empty($db_item['plot']) ? $update_fields['plot'] = $db_item['plot'] : null;
    !empty($db_item['release']) ? $update_fields['release'] = $db_item['release'] : null;

    $db->updateItemById($library, $id, $update_fields);
}

function getLibraryStats() {
    global $db, $cfg;

    $stats['movies_size'] = 0;
    $stats['shows_size'] = 0;

    $movies_db = $db->getTableData('library_movies');
    $stats['num_movies'] = count($movies_db);

    if (!empty($movies_db)) {
        foreach ($movies_db as $db_movie) {
            if (isset($db_movie['size'])) {
                $stats['movies_size'] = $stats['movies_size'] + $db_movie['size'];
            }
        }
        $stats['movies_size'] = human_filesize($stats['movies_size']);
    }

    $shows_db = $db->getTableData('library_shows');
    $stats['num_episodes'] = count($shows_db);
    $count_shows = [];

    if (!empty($shows_db)) {
        foreach ($shows_db as $db_show) {
            if (isset($db_show['size'])) {
                $stats['shows_size'] = $stats['shows_size'] + $db_show['size'];
            }

            if (!empty($db_show['themoviedb_id'])) {
                $tmdb_id = $db_show['themoviedb_id'];
                if (!isset($count_shows[$tmdb_id])) {
                    $count_shows[$tmdb_id] = 1;
                }
            }
        }
        $stats['shows_size'] = human_filesize($stats['shows_size']);
    }

    $stats['num_shows'] = count($count_shows);

    $stats['db_size'] = file_exists($cfg['DB_FILE']) ? human_filesize(filesize($cfg['DB_FILE'])) : 0;
    return $stats;
}
