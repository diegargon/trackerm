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
    global $cfg, $db;


    $items = [];
    $files = findfiles($path, $cfg['media_ext']);

    if ($media_type == 'movies') {
        $db_file = 'biblio-movies';
        $ilink = 'movies_library';
    } else if ($media_type == 'shows') {
        $db_file = 'biblio-shows';
        $ilink = 'shows_library';
    }

    $media = $db->getTableData($db_file);
    $last_id = $db->getLastId($db_file);

    foreach ($files as $file) {
        if ($media === false ||
                array_search($file, array_column($media, 'path')) === false
        ) {
            $items[$last_id]['id'] = $last_id;
            $items[$last_id]['ilink'] = $ilink;
            /* File */
            $items[$last_id]['file_name'] = $file_name = trim(basename($file));
            $items[$last_id]['size'] = filesize($file);

            /* TITLE  */
            $predictible_title = getFileTitle($file_name);
            $items[$last_id]['predictible_title'] = ucwords($predictible_title);
            $items[$last_id]['title'] = '';
            $year = getFileYear($file_name);
            !empty($year) ? $items[$last_id]['year'] = $year : null;
            $items[$last_id]['path'] = $file;
            $items[$last_id]['tags'] = getFileTags($file_name);
            $items[$last_id]['ext'] = substr($file_name, -3);
            $items[$last_id]['added'] = time();
            $chapter = getFileEpisode($file_name);
            if (!empty($chapter)) {
                $items[$last_id]['season'] = intval($chapter['season']);
                $items[$last_id]['chapter'] = intval($chapter['chapter']);
            }

            /* auto identify episodes already identified */
            if ($media_type == 'shows') {
                foreach ($media as $id_item) {

                    if ($id_item['predictible_title'] === ucwords($predictible_title) &&
                            !empty($id_item['themoviedb_id'])
                    ) {
                        $items[$last_id]['themoviedb_id'] = $id_item['themoviedb_id'];
                        $items[$last_id]['title'] = $id_item['title'];
                        $items[$last_id]['poster'] = $id_item['poster'];
                        $items[$last_id]['rating'] = $id_item['rating'];
                        $items[$last_id]['popularity'] = $id_item['popularity'];
                        $items[$last_id]['scene'] = $id_item['scene'];
                        $items[$last_id]['lang'] = $id_item['lang'];
                        $items[$last_id]['plot'] = $id_item['plot'];
                        $items[$last_id]['original_title'] = $id_item['original_title'];
                    }
                }
            }
            $last_id++;
        }
    }

    isset($items) ? $db->addElements($db_file, $items) : null;

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
    global $db;

    foreach ($items as $my_id => $db_id) {
        if (!empty($db_id)) {
            $db_item = mediadb_getByLocalId($db_id);

            !empty($db_item['title']) ? $update_fields['title'] = $db_item['title'] : null;
            !empty($db_item['name']) ? $update_fields['title'] = $db_item['name'] : null;
            $update_fields['themoviedb_id'] = $db_item['themoviedb_id'];
            !empty($db_item['poster']) ? $update_fields['poster'] = $db_item['poster'] : null;
            !empty($db_item['chapter']) ? $update_fields['chapter'] = $db_item['chapter'] : null;
            !empty($db_item['season']) ? $update_fields['season'] = $db_item['season'] : null;
            !empty($db_item['original_title']) ? $update_fields['original_title'] = $db_item['original_title'] : null;
            !empty($db_item['rating']) ? $update_fields['rating'] = $db_item['rating'] : null;
            !empty($db_item['popularity']) ? $update_fields['popularity'] = $db_item['popularity'] : null;
            !empty($db_item['scene']) ? $update_fields['scene'] = $db_item['scene'] : null;
            !empty($db_item['lang']) ? $update_fields['lang'] = $db_item['lang'] : null;
            !empty($db_item['plot']) ? $update_fields['plot'] = $db_item['plot'] : null;
            !empty($db_item['release']) ? $update_fields['release'] = $db_item['release'] : null;
            if ($type == 'movies') {
                $db->updateRecordById('biblio-movies', $my_id, $update_fields);
            } else if ($type == 'shows') {
                $db->updateRecordsBySameField('biblio-shows', $my_id, 'predictible_title', $update_fields);
            }
        }
    }
}

function getLibraryStats() {
    global $db;

    $stats['movies_size'] = 0;
    $stats['shows_size'] = 0;

    $movies_db = $db->getTableData('biblio-movies');
    $stats['num_movies'] = $db->getNumElements('biblio-movies');

    if (!empty($movies_db)) {
        foreach ($movies_db as $db_movie) {
            if (isset($db_movie['size'])) {
                $stats['movies_size'] = $stats['movies_size'] + $db_movie['size'];
            }
        }
        $stats['movies_size'] = human_filesize($stats['movies_size']);
    }

    $shows_db = $db->getTableData('biblio-shows');
    $stats['num_episodes'] = $db->getNumElements('biblio-shows');
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
