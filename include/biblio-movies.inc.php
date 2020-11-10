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

        $page .= buildTable('L_MOVIES', $movies_identifyed);
    }
    return $page;
}
