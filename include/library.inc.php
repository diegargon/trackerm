<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function show_my_movies() {
    global $newdb, $cfg;

    $page = '';

    if (isset($_POST['mult_movies_select']) && !empty($_POST['mult_movies_select'])) {
        submit_ident('movies', $_POST['mult_movies_select']);
    }
    if (!empty($_GET['ident_delete']) && ($_GET['media_type'] == 'movies')) {
        $newdb->deleteItemById('library_movies', $_GET['ident_delete']);
    }

    $movies = $newdb->getTableData('library_movies');
    if (isset($_POST['rebuild_movies'])) {
        rebuild('movies', $cfg['MOVIES_PATH']);
        $movies = $newdb->getTableData('library_movies');
    }

    if ($movies != false) {
        $page .= identify_media('movies', $movies);
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
    global $newdb, $cfg;

    $page = '';
    $topt = [];

    if (isset($_POST['mult_shows_select']) && !empty($_POST['mult_shows_select'])) {
        submit_ident('shows', $_POST['mult_shows_select']);
    }

    if (!empty($_GET['ident_delete']) && ($_GET['media_type'] == 'shows')) {
        $delete_ident_item = $newdb->getItemById('library_shows', $_GET['ident_delete']);
        $delete_ptitle_match = $delete_ident_item['predictible_title'];
        $newdb->deleteItemByField('library_shows', 'predictible_title', $delete_ptitle_match);
    }
    $shows = $newdb->getTableData('library_shows');

    if (isset($_POST['rebuild_shows'])) {
        rebuild('shows', $cfg['SHOWS_PATH']);
        $shows = $newdb->getTableData('library_shows');
    }

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

        $sizes = [];
        $have_episodes = [];

        foreach ($shows_identifyed as $show) {
            $exists = false;
            if (isset($sizes[$show['themoviedb_id']])) {
                $sizes[$show['themoviedb_id']] = $show['size'] + $sizes[$show['themoviedb_id']];
                $have_episodes[$show['themoviedb_id']] = 1 + $have_episodes[$show['themoviedb_id']];
            } else {
                $sizes[$show['themoviedb_id']] = $show['size'];
                $have_episodes[$show['themoviedb_id']] = 1;
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
        count($sizes) > 1 ? $topt['sizes'] = $sizes : null;
        count($have_episodes) > 1 ? $topt['have_episodes'] = $have_episodes : null;

        $topt['search_type'] = 'shows';
        $page .= buildTable('L_SHOWS', $uniq_shows, $topt);
    }
    return $page;
}
