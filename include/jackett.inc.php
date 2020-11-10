<?php

/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function search_movie_torrents($words, $head = null) {
    global $cfg;

    $result = [];
    $page = '';

    $words = str_replace(' ', '%20', $words);
    foreach ($cfg['jackett_indexers'] as $indexer) {
        $result[$indexer] = torznab_search_movies($words, $indexer);
    }

    $movies_db = torznab_prep_movies($result);

    $page .= buildTable($head, $movies_db);

    return $page;
}

function search_shows_torrents($words, $head = null) {
    global $cfg;

    $result = [];

    $page = '';

    $words = str_replace(' ', '%20', $words);
    foreach ($cfg['jackett_indexers'] as $indexer) {
        $result[$indexer] = torznab_search_shows($words, $indexer);
    }

    $shows_db = torznab_prep_shows($result);

    $page .= buildTable($head, $shows_db);

    return $page;
}
