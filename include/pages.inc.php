<?php

/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function index_page() {
    
}

function page_view() {
    return view();
}

function page_biblio() {
    global $LNG;

    $page = '';
    $page .= '<form method="post">';
    $page .= '<p><input class="submit_btn" type="submit" name="rebuild_movies" value="' . $LNG['L_REBUILD_MOVIES'] . '"/>';
    $page .= '<input class="submit_btn" type="submit" name="rebuild_shows" value="' . $LNG['L_REBUILD_SHOWS'] . '"/></p>';
    //$page .= '<p>Buscador:<input type="text" name=""><input type="submit" name="search" value="' . $LNG['L_SEARCH'] . ' "></p>';
    $page .= '</form>';

    $page .= show_my_movies();
    $page .= show_my_shows();

    return $page;
}

function page_news() {
    global $cfg;

    foreach ($cfg['jackett_indexers'] as $indexer) {
        $results = jackett_search_movies('', $indexer);
        ($results) ? $movies_res[$indexer] = $results : null;
        $results = null;
        $results = jackett_search_shows('', $indexer);
        $results ? $shows_res[$indexer] = $results : null;
    }

    $res_movies_db = jackett_prep_movies($movies_res);
    $res_shows_db = jackett_prep_shows($shows_res);

    /* BUILD PAGE */

    $page_news = '';

    if (!empty($res_movies_db)) {
        $page_news_movies = buildTable('L_MOVIES', $res_movies_db);
        $page_news .= $page_news_movies;
    }

    if (!empty($res_shows_db)) {
        $page_news_shows = buildTable('L_SHOWS', $res_shows_db);
        $page_news .= $page_news_shows;
    }

    return $page_news;
}

function page_tmdb() {
    global $LNG;

    $page = '<h2>TheMovieDb.org</h2>';
    $page .= '<a href="https://themoviedb.org" target=_blank>The Movie Database</a>';
    $page .= '<form method="post">';
    $page .= '<p>Buscar Pelicula:<input type="text" name="search_movie">';
    $page .= '<p>Buscar Serie:<input type="text" name="search_shows"></p>';
    $page .= '<input type="submit" name="search" value="' . $LNG['L_SEARCH'] . ' "></p>';
    $page .= '</form>';

    if (!empty($_POST['search_movie'])) {
        $movies = db_search_movies(trim($_POST['search_movie']));
        !empty($movies) ? $page .= buildTable('L_DB', $movies) : null;
    }

    if (!empty($_POST['search_shows'])) {
        $shows = db_search_shows(trim($_POST['search_shows']));
        !empty($shows) ? $page .= buildTable('L_DB', $shows) : null;
    }

    return $page;
}

function page_torrents() {
    global $LNG;

    $page = '<h2>' . $LNG['L_SEARCHTORRENTS'] . '</h2>';
    $page .= '<p>' . $LNG['L_SEARCHTORRENTS_DESC'] . '</p>';
    $page .= '<form method="post">';
    $page .= '<p>Buscar Serie:<input type="text" name="search_shows_torrents"></p>';
    $page .= '<p>Buscar Pelicula:<input type="text" name="search_movie_torrents"></p>';
    $page .= '<input type="submit" name="search" value="' . $LNG['L_SEARCH'] . ' "></p>';
    $page .= '</form>';


    if (!empty($_POST['search_shows_torrents'])) {
        $page .= search_shows_torrents(trim($_POST['search_shows_torrents']), 'L_TORRENT');
    }
    if (!empty($_POST['search_movie_torrents'])) {
        $page .= search_movie_torrents(trim($_POST['search_movie_torrents']), 'L_TORRENT');
    }

    return $page;
}
