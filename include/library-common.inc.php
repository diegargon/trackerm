<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function rebuild($media_type, $path) {
    global $cfg, $newdb;


    $items = [];
    $files = findfiles($path, $cfg['media_ext']);

    if ($media_type == 'movies') {
        $library_table = 'library_movies';
        $ilink = 'movies_library';
    } else if ($media_type == 'shows') {
        $library_table = 'library_shows';
        $ilink = 'shows_library';
    }

    $media = $newdb->getTableData($library_table);

    $i = 0;
    foreach ($files as $file) {
        if ($media === false ||
                array_search($file, array_column($media, 'path')) === false
        ) {

            $file_name = trim(basename($file));
            $predictible_title = getFileTitle($file_name);
            $year = getFileYear($file_name);
            $tags = getFileTags($file_name);
            $ext = substr($file_name, -3);


            $items[$i] = [
                'ilink' => $ilink,
                'file_name' => $file_name,
                'size' => filesize($file),
                'predictible_title' => ucwords($predictible_title),
                'title' => '',
                'title_year' => $year,
                'path' => $file,
                'tags' => $tags,
                'ext' => $ext,
            ];
            /*
              $items[$i]['ilink'] = $ilink;
              $items[$i]['file_name'] = $file_name;
              $items[$i]['size'] = filesize($file);
              $items[$i]['predictible_title'] = ucwords($predictible_title);
              $items[$i]['title'] = '';
              $items[$i]['title_year'] = $year;
              $items[$i]['path'] = $file;
              $items[$i]['tags'] = $tags;
              $items[$i]['ext'] = $ext;
             */
            if ($media_type == 'shows') {
                $SE = getFileEpisode($file_name);

                if (!empty($SE)) {
                    $season = intval($SE['season']);
                    $episode = intval($SE['episode']);
                }
                $items[$i]['season'] = $season;
                $items[$i]['episode'] = $episode;
            }

            // auto identify episodes already identified
            if ($media_type == 'shows') {
                foreach ($media as $id_item) {

                    if ($id_item['predictible_title'] === ucwords($predictible_title) &&
                            !empty($id_item['themoviedb_id'])
                    ) {
                        $items[$i]['themoviedb_id'] = $id_item['themoviedb_id'];
                        $items[$i]['title'] = $id_item['title'];
                        $items[$i]['poster'] = $id_item['poster'];
                        $items[$i]['rating'] = $id_item['rating'];
                        $items[$i]['popularity'] = $id_item['popularity'];
                        $items[$i]['scene'] = $id_item['scene'];
                        $items[$i]['lang'] = $id_item['lang'];
                        $items[$i]['plot'] = $id_item['plot'];
                        $items[$i]['original_title'] = $id_item['original_title'];
                    }
                }
            }
        }
        $i++;
    }
    isset($items) ? $newdb->addItems($library_table, $items) : null;

    return true;
}

function identify_media($type, $media) {
    global $LNG, $cfg;

    $titles = '';
    $i = 0;
    $uniq_shows = [];

    $iurl = '?page=' . $_GET['page'];

    foreach ($media as $item) {
        if (empty($item['title'])) {
            if ($i >= $cfg['max_identify_items']) {
                break;
            }
            if ($type == 'movies') {
                $db_media = mediadb_searchMovies($item['predictible_title']);
            } else if ($type == 'shows') {
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
            $results_opt = '';
            if (!empty($db_media)) {

                foreach ($db_media as $db_item) {
                    $year = trim(substr($db_item['release'], 0, 4));
                    $results_opt .= '<option value="' . $db_item['id'] . '">';
                    $results_opt .= $db_item['title'];
                    !empty($year) ? $results_opt .= ' (' . $year . ')' : null;
                    $results_opt .= '</option>';
                }
            }
            $results_opt .= '<option value="">' . $LNG['L_NOID'] . '</option>';
            $titles .= '<div class="divTableRow"><div class="divTableCellID">' . $item['predictible_title'] . '</div>';
            $titles .= '<div class="divTableCellID">';
            if ($type == 'movies') {
                $titles .= '<select class="ident_select" name="mult_movies_select[' . $item['id'] . ']">' . $results_opt . '</select>';
            } else if ($type == 'shows') {
                $titles .= '<select class="ident_select" name="mult_shows_select[' . $item['id'] . ']">' . $results_opt . '</select>';
            }
            $titles .= '</div>';
            $titles .= '<div class="divTableCellID">';
            $titles .= '<span><a class="action_link" href="' . $iurl . '&media_type=' . $type . '&ident_delete=' . $item['id'] . '">' . $LNG['L_DELETE'] . '</a></span>';
            $titles .= '<span><a class="action_link" href="?page=identify&media_type=' . $type . '&identify=' . $item['id'] . '">' . $LNG['L_MORE'] . '</a></span>';
            $titles .= '</div>';
            $titles .= '</div>';

            $i++;
        }
    }

    if (!empty($titles)) {
        $tdata['titles'] = $titles;
        $tdata['type'] = $type;
        $tdata['head'] = $LNG['L_IDENT_' . strtoupper($type) . ''];

        $table = getTpl('identify', array_merge($LNG, $tdata));

        return $table;
    }
    return false;
}

function submit_ident($type, $items) {
    global $newdb;


    foreach ($items as $my_id => $db_id) {
        if (!empty($db_id)) {
            $db_item = mediadb_getByLocalId($db_id);

            !empty($db_item['title']) ? $update_fields['title'] = $db_item['title'] : null;
            !empty($db_item['name']) ? $update_fields['title'] = $db_item['name'] : null;
            $update_fields['themoviedb_id'] = $db_item['themoviedb_id'];
            !empty($db_item['poster']) ? $update_fields['poster'] = $db_item['poster'] : null;
            //!empty($db_item['episode']) ? $update_fields['episode'] = $db_item['episode'] : null;
            //!empty($db_item['season']) ? $update_fields['season'] = $db_item['season'] : null;
            !empty($db_item['original_title']) ? $update_fields['original_title'] = $db_item['original_title'] : null;
            !empty($db_item['rating']) ? $update_fields['rating'] = $db_item['rating'] : null;
            !empty($db_item['popularity']) ? $update_fields['popularity'] = $db_item['popularity'] : null;
            !empty($db_item['scene']) ? $update_fields['scene'] = $db_item['scene'] : null;
            !empty($db_item['lang']) ? $update_fields['lang'] = $db_item['lang'] : null;
            !empty($db_item['plot']) ? $update_fields['plot'] = $db_item['plot'] : null;
            !empty($db_item['release']) ? $update_fields['release'] = $db_item['release'] : null;

            if ($type == 'movies') {
                $newdb->updateItemById('library_movies', $my_id, $update_fields);
            } else if ($type == 'shows') {
                $mylib_show = $newdb->getItemById('library_shows', $my_id);
                $update_fields['predictible_title'] = $mylib_show['predictible_title'];
                $newdb->updateItemsByField('library_shows', $update_fields, 'predictible_title');
            }
        }
    }
}

function getLibraryStats() {
    global $newdb;

    $stats['movies_size'] = 0;
    $stats['shows_size'] = 0;

    $movies_db = $newdb->getTableData('library_movies');
    $stats['num_movies'] = $newdb->count('library_movies');

    if (!empty($movies_db)) {
        foreach ($movies_db as $db_movie) {
            if (isset($db_movie['size'])) {
                $stats['movies_size'] = $stats['movies_size'] + $db_movie['size'];
            }
        }
        $stats['movies_size'] = human_filesize($stats['movies_size']);
    }

    $shows_db = $newdb->getTableData('library_shows');
    $stats['num_episodes'] = $newdb->count('library_shows');
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

    return $stats;
}
