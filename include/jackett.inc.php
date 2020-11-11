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
        $result[$indexer] = jackett_search_movies($words, $indexer);
    }

    $movies_db = jackett_prep_movies($result);
    $page .= buildTable($head, $movies_db);

    return $page;
}

function search_shows_torrents($words, $head = null) {
    global $cfg;

    $result = [];

    $page = '';

    $words = str_replace(' ', '%20', $words);
    foreach ($cfg['jackett_indexers'] as $indexer) {
        $result[$indexer] = jackett_search_shows($words, $indexer);
    }

    $shows_db = jackett_prep_shows($result);
    $page .= buildTable($head, $shows_db);

    return $page;
}

/*
 * http://192.168.:9117/api/v2.0/indexers/newpct/results/torznab/api?t=tvsearch&cat=5030,5040&extended=1&apikey=&offset=0&limit=100
 * 
 * rsss feed:
 * 192.168.:9117/api/v2.0/indexers/newpct/results/torznab/api?apikey=k&t=search&cat=&q=
 * 
 * get capat
 * http://192.168.:9117/api/v2.0/indexers/newpct/results/torznab/api?apikey=c&t=caps
 */

function jackett_get($indexer, $limit = null) {
    global $cfg;

    if (empty($limit)) {
        $limit = $cfg['jacket_results'];
    }

    //$jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] .'/indexers/'. $indexer . '/results/torznab/api?apikey=' . $cfg['jackett_key'] . '&t=search&cat=2000&extended=1&q=';
    //$jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] .'/indexers/'. $indexer . '/results/torznab/api?apikey=' . $cfg['jackett_key'] . '&t=tvsearch&extended=1&q=&limit=5';
    //$jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] .'/indexers/'. $indexer . '/results/torznab/api?apikey=' . $cfg['jackett_key'] . '&t=movie&cat=2000&q=';
    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/api?t=search&extended=1&apikey=' . $cfg['jackett_key'] . '&limit=' . $limit;
    //echo $jackett_url . "</br>";

    return curl_get_jackett($jackett_url);
}

function jackett_get_caps($indexer) {
    global $cfg;

    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/api?apikey=' . $cfg['jackett_key'] . '&t=caps';
    return curl_get_jackett($jackett_url);
}

function jackett_search_movies($words, $indexer, $limit = null) {
    global $cfg;

    if (empty($limit)) {
        $limit = $cfg['jacket_results'];
    }

    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/api?apikey=' . $cfg['jackett_key'] . '&t=search&extended=1&cat=2000&q=' . $words . '&limit=' . $limit;

    return curl_get_jackett($jackett_url);
}

function jackett_search_shows($words, $indexer, $limit = null) {
    global $cfg;

    if (empty($limit)) {
        $limit = $cfg['jacket_results'];
    }
    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/api?apikey=' . $cfg['jackett_key'] . '&t=search&extended=1&cat=5000&q=' . $words . '&limit=' . $limit;

    return curl_get_jackett($jackett_url);
}

function jackett_prep_movies($movies_results) {
    global $db;

    foreach ($movies_results as $indexer) {

        if (isset($indexer['channel']['item']['title'])) {
            $movie = $indexer['channel']['item'];
            isset($movie['files']) ? $files = $movie['files'] : $files = '';

            $torznab = $movie['torznab'];
            foreach ($torznab as $attr) {
                $movie[$attr['@attributes']['name']] = $attr['@attributes']['value'];
            }

            isset($movie['coverurl']) ? $poster = $movie['coverurl'] : $poster = '';
            !empty($movie['description']) ? $description = $movie['description'] : $description = '';

            $movies[] = [
                'id' => '',
                'guid' => $movie['guid'],
                'title' => $movie['title'],
                'lang' => '',
                'release' => $movie['pubDate'],
                'size' => $movie['size'],
                'plot' => $description,
                'files' => $files,
                'download' => $movie['link'],
                'category' => $movie['category'],
                'source' => $movie['jackettindexer'],
                'poster' => $poster,
                'added' => time(),
            ];
        } else if (isset($indexer['channel']['item'])) {
            foreach ($indexer['channel']['item'] as $movie) {
                isset($movie['files']) ? $files = $movie['files'] : $files = '';

                $torznab = $movie['torznab'];
                foreach ($torznab as $attr) {
                    $movie[$attr['@attributes']['name']] = $attr['@attributes']['value'];
                }
                !empty($movie['coverurl']) ? $poster = $movie['coverurl'] : $poster = '';
                !empty($movie['description']) ? $description = $movie['description'] : $description = '';
                $movies[] = [
                    'id' => '',
                    'guid' => $movie['guid'],
                    'title' => $movie['title'],
                    'lang' => '',
                    'release' => $movie['pubDate'],
                    'size' => $movie['size'],
                    'plot' => $description,
                    'files' => $files,
                    'download' => $movie['link'],
                    'category' => $movie['category'],
                    'source' => $movie['jackettindexer'],
                    'poster' => $poster,
                    'added' => time(),
                ];
            }
        }
    }
    isset($movies) ? $db->addUniqElements('jackett_movies', $movies, 'guid') : null;

    //add ID's
    foreach ($movies as $key => $movie) {
        $id = $db->getIdbyField('jackett_movies', 'guid', $movie['guid']);
        $movies[$key]['id'] = $id;
    }
    return $movies;
}

function jackett_prep_shows($shows_results) {
    global $db;

    $shows = [];
    foreach ($shows_results as $indexer) {

        if (isset($indexer['channel']['item']['title'])) {
            $show = $indexer['channel']['item'];
            isset($show['files']) ? $files = $show['files'] : $files = '';

            $torznab = $show['torznab'];
            foreach ($torznab as $attr) {
                $show[$attr['@attributes']['name']] = $attr['@attributes']['value'];
            }
            !empty($show['coverurl']) ? $poster = $show['coverurl'] : $poster = '';
            !empty($show['description']) ? $description = $show['description'] : $description = '';

            $shows[] = [
                'id' => '',
                'guid' => $show['guid'],
                'title' => $show['title'],
                'lang' => $show['language'],
                'release' => $show['pubDate'],
                'size' => $show['size'],
                'plot' => $description,
                'files' => $files,
                'download' => $show['link'],
                'category' => $show['category'],
                'source' => $show['jackettindexer'],
                'poster' => $poster,
                'added' => time(),
            ];
        } else if (isset($indexer['channel']['item'])) {
            foreach ($indexer['channel']['item'] as $show) {
                isset($show['files']) ? $files = $show['files'] : $files = '';

                $torznab = $show['torznab'];
                foreach ($torznab as $attr) {
                    $show[$attr['@attributes']['name']] = $attr['@attributes']['value'];
                }
                !empty($show['coverurl']) ? $poster = $show['coverurl'] : $poster = '';
                !empty($show['description']) ? $description = $show['description'] : $description = '';
                $shows[] = [
                    'id' => '',
                    'guid' => $show['guid'],
                    'title' => $show['title'],
                    'lang' => '',
                    'release' => $show['pubDate'],
                    'size' => $show['size'],
                    'plot' => $description,
                    'files' => $files,
                    'download' => $show['link'],
                    'category' => $show['category'],
                    'source' => $show['jackettindexer'],
                    'poster' => $poster,
                    'added' => time(),
                ];
            }
        }
    }
    isset($shows) ? $db->addUniqElements('jackett_shows', $shows, 'guid') : null;

    //add ID's
    foreach ($shows as $key => $show) {
        $id = $db->getIdbyField('jackett_shows', 'guid', $show['guid']);
        $shows[$key]['id'] = $id;
    }
    return $shows;
}
