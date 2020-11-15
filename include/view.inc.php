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

    if (!empty($item['poster']) && $cfg['CACHE_IMAGES']) {
        $cache_img_response = get_and_cache_img($item['poster']);
        if ($cache_img_response !== false) {
            $item['poster'] = $cache_img_response;
        }
    }
    $other['extra'] = '';

    $opt['auto_show_torrents'] = 0;
    $opt['auto_show_db'] = 0;


    ($type == 'movies_db' || $type == 'shows_db') ? $opt['auto_show_torrents'] = 1 : null;
    ($type == 'movies_torrent' || $type == 'shows_torrent') ? $opt['auto_show_db'] = 1 : null;


    if ($type == 'movies_torrent' || $type == 'movies_db' || $type == 'movies_library') {
        $other['extra'] .= view_extra_movies($item, $opt);
    }

    if ($type == 'shows_torrent' || $type == 'shows_db' || $type == 'shows_library') {
        $other['extra'] .= view_extra_shows($item, $opt);
    }

    $page = getTpl('view', array_merge($cfg, $LNG, $item, $other));

    return $page;
}

function view_extra_movies($item, $opt = null) {
    global $LNG;
    $extra = '';

    $extra .= '<form method="post">';
    $extra .= '<input class="submit_btn" type="submit" name="more_movies" value="' . $LNG['L_SEARCH_MOVIES'] . '" >';
    $extra .= '<input class="submit_btn" type="submit" name="more_torrents" value="' . $LNG['L_SHOW_TORRENTS'] . '" >';

    $title = getFileTitle(trim($item['title']));


    if (!empty($_POST['search_movie_db'])) {
        $stitle = trim($_POST['search_movie_db']);
    } else {
        $stitle = $title;
    }
    $extra .= '<input type="text" name="search_movie_db" value="' . $stitle . '">';

    if (isset($_POST['more_movies']) || !empty($opt['auto_show_db'])) {
        $movies = db_search_movies($stitle);
        !empty($movies) ? $extra .= buildTable('L_DB', $movies, $opt) : null;
    }
    $extra .= '</form>';


    if (isset($_POST['more_torrents']) || !empty($opt['auto_show_torrents'])) {
        $extra .= search_movie_torrents($stitle);
    }

    return $extra;
}

function view_extra_shows($item, $opt) {
    global $LNG;

    $extra = '';

    $extra .= '<form method="post">';
    $extra .= '<input class="submit_btn" type="submit" name="more_shows" value="' . $LNG['L_SEARCH_SHOWS'] . '" >';
    $extra .= '<input class="submit_btn" type="submit" name="more_torrents" value="' . $LNG['L_SHOW_TORRENTS'] . '" >';
    $title = getFileTitle(trim($item['title']));

    if (!empty($_POST['search_shows_db'])) {
        $stitle = trim($_POST['search_shows_db']);
    } else {
        $stitle = $title;
    }

    $extra .= '<input type="text" name="search_shows_db" value="' . $stitle . '">';

    if (isset($_POST['more_shows']) || !empty($opt['auto_show_db'])) {
        $shows = db_search_shows($stitle);
        !empty($shows) ? $extra .= buildTable('L_DB', $shows, $opt) : null;
    }

    if (isset($_POST['more_torrents']) || !empty($opt['auto_show_torrents'])) {
        $title = getFileTitle($item['title']);
        $extra .= search_shows_torrents($title);
    }
    $extra .= '</form>';

    return $extra;
}
