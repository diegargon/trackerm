<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function show_my_movies() {
    global $db, $cfg;

    $page = '';

    if (!empty($_POST['mult_movies_select'])) {
        user_submit_ident('movies', $_POST['mult_movies_select']);
    }
    if (!empty($_GET['ident_delete']) && ($_GET['media_type'] == 'movies')) {
        $db->deleteItemById('library_movies', $_GET['ident_delete']);
    }

    $movies = $db->getTableData('library_movies');

    if ($movies != false) {
        $page .= show_identify_media('movies', $movies);
        $movies_identifyed = [];

        foreach ($movies as $key => $movie) {
            if (!empty($movie['title'])) {
                $movies_identifyed[$key] = $movie;
            }
        }

        usort($movies_identifyed, function ($a, $b) {
            return strcmp($a["created"], $b["created"]);
        });
        $movies_identifyed = array_reverse($movies_identifyed);

        $topt['search_type'] = 'movies';
        $page .= buildTable('L_MOVIES', $movies_identifyed, $topt);
    }
    return $page;
}

function show_my_shows() {
    global $db, $cfg;

    $page = '';
    $topt = [];

    if (isset($_POST['mult_shows_select']) && !empty($_POST['mult_shows_select'])) {
        submit_ident('shows', $_POST['mult_shows_select']);
    }

    if (!empty($_GET['ident_delete']) && ($_GET['media_type'] == 'shows')) {
        $delete_ident_item = $db->getItemById('library_shows', $_GET['ident_delete']);
        $delete_ptitle_match = $delete_ident_item['predictible_title'];
        $db->deleteItemByField('library_shows', 'predictible_title', $delete_ptitle_match);
    }
    $shows = $db->getTableData('library_shows');

    if ($shows != false) {

        $page .= identify_media('shows', $shows);

        $shows_identifyed = [];

        foreach ($shows as $key => $movie) {
            if (!empty($movie['title'])) {
                $shows_identifyed[$key] = $movie;
            }
        }

        usort($shows_identifyed, function ($a, $b) {
            return strcmp($a["created"], $b["created"]);
        });
        $shows_identifyed = array_reverse($shows_identifyed);

        $uniq_shows = [];
        $sum_sizes = [];
        $episode_count = [];

        foreach ($shows_identifyed as $show) {
            $exists = false;

            if (isset($sum_sizes[$show['themoviedb_id']])) {
                $sum_sizes[$show['themoviedb_id']] = $show['size'] + $sum_sizes[$show['themoviedb_id']];
            } else {
                $sum_sizes[$show['themoviedb_id']] = $show['size'];
            }

            if (isset($episode_count[$show['themoviedb_id']])) {
                $episode_count[$show['themoviedb_id']] = 1 + $episode_count[$show['themoviedb_id']];
            } else {
                $episode_count[$show['themoviedb_id']] = 1;
            }

            foreach ($uniq_shows as $item) {
                if ($item['title'] == $show['title']) {
                    $exists = true;
                    break;
                } else {
                    $exists = false;
                }
            }

            $exists === false ? $uniq_shows[] = $show : null;
        }
        count($sum_sizes) > 1 ? $topt['sizes'] = $sum_sizes : null;
        count($episode_count) > 1 ? $topt['episode_count'] = $episode_count : null;

        $topt['search_type'] = 'shows';
        $page .= buildTable('L_SHOWS', $uniq_shows, $topt);
    }
    return $page;
}
