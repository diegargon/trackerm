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
    global $LNG, $cfg;

    isset($_POST['num_id_show']) ? $cfg['max_identify_items'] = $_POST['num_id_show'] : null;
    ($cfg['max_identify_items'] == 10) ? $max_id_sel_10 = 'selected' : $max_id_sel_10 = '';
    ($cfg['max_identify_items'] == 20) ? $max_id_sel_20 = 'selected' : $max_id_sel_20 = '';
    ($cfg['max_identify_items'] == 50) ? $max_id_sel_50 = 'selected' : $max_id_sel_50 = '';

    $tdata['max_id_sel_10'] = $max_id_sel_10;
    $tdata['max_id_sel_20'] = $max_id_sel_20;
    $tdata['max_id_sel_50'] = $max_id_sel_50;

    $page = getTpl('library_options', array_merge($tdata, $LNG));

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

    $page = getTpl('page_tmdb', $LNG);

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

    $page = getTpl('page_torrents', $LNG);

    if (!empty($_POST['search_shows_torrents'])) {
        $page .= search_shows_torrents(trim($_POST['search_shows_torrents']), 'L_TORRENT');
    }
    if (!empty($_POST['search_movie_torrents'])) {
        $page .= search_movie_torrents(trim($_POST['search_movie_torrents']), 'L_TORRENT');
    }

    return $page;
}
