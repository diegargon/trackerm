<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function show_my_movies() {
    global $db, $cfg;

    $page = '';

    if (isset($_POST['mult_movies_select']) && !empty($_POST['mult_movies_select'])) {
        submit_ident('movies', $_POST['mult_movies_select']);
    }
    if (!empty($_GET['ident_delete']) && !empty($_GET['media_type'])) {
        if ($_GET['media_type'] === 'movies') {
            $db->deleteByID('biblio-movies', $_GET['ident_delete']);
        }
        if ($_GET['media_type'] === 'shows') {
            $db->deleteByID('biblio-shows', $_GET['ident_delete']);
        }
    }

    $movies = $db->getTableData('biblio-movies');
    if ($movies == false || isset($_POST['rebuild_movies'])) {
        rebuild('movies', $cfg['MOVIES_PATH']);
        $movies = $db->getTableData('biblio-movies');
    }

    if ($movies != false) {
        $page .= identify_media('movies', $movies);
        $movies_identifyed = [];

        foreach ($movies as $key => $movie) {
            if (!empty($movie['title'])) {
                $movies_identifyed[$key] = $movie;
            }
        }
        $topt['search_type'] = 'movies';
        $page .= buildTable('L_MOVIES', $movies_identifyed, $topt);
    }
    return $page;
}
