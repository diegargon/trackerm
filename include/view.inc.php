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

    if ($type == 'movies_library') {
        $t_type = 'biblio-movies';
    } else if ($type == 'shows_library') {
        $t_type = 'biblio-shows';
    } else if ($type == 'movies_torrent') {
        $t_type = 'jackett_movies';
    } else if ($type == 'shows_torrent') {
        $t_type = 'jackett_shows';
    } else if ($type == 'movies_db') {
        $t_type = 'tmdb_search';
    } else if ($type == 'shows_db') {
        $t_type = 'tmdb_search';
    } else {
        return false;
    }

    $item = $db->getItemByID($t_type, $id);

    $other['extra'] = '';

    if ($type == 'movies_library') {
        $other['extra'] = view_extra_movies($item);
    }
    if ($type == 'movies_db') {
        $other['extra'] = view_extra_movies($item);
    }
    if ($type == 'shows_library') {
        $other['extra'] = view_extra_shows($item);
    }
    if ($type == 'shows_db') {
        $other['extra'] = view_extra_shows($item);
    }

    if ($type == 'movies_torrent') {

    }
    if ($type == 'shows_torrent') {
        
    }
    $page = getTpl('view', array_merge($cfg, $LNG, $item, $other));
    
    return $page;
}

function view_extra_movies($item) {
    $extra = search_movie_torrents($item['title']);

    return $extra;
}

function view_extra_shows($item) {
    $extra = search_shows_torrents($item['title']);

    return $extra;
}
