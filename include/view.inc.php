<?php

/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function view() {
    global $cfg, $LNG, $db;
    $type = $_GET['type'];
    $id = $_GET['id'];

    $page = '';

    if ($type == 'movie_library') {
        $type = 'biblio-movies';
    } else if ($type == 'shows_library') {
        $type = 'biblio-shows';
    } else if ($type == 'movies_torrent') {
        $type = 'jackett_movies';
    } else if ($type == 'shows_torrent') {
        $type = 'jackett_shows';
    } else if ($type == 'movies_db') {
        $type = 'tmdb_search';
    } else if ($type == 'shows_db') {
        $type = 'tmdb_search';
    } else {
        return false;
    }

    //$items = $db->getItemByID($type, $id);

    $page = getTpl('view', array_merge($cfg, $LNG));
    return $page;
}
