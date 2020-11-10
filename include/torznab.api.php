<?php

/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 * 
 * http://192.168.2.10:9117/api/v2.0/indexers/newpct/results/torznab/api?t=tvsearch&cat=5030,5040&extended=1&apikey=k1ryk9av87gxjk9e9sj7kpka5mommaxc&offset=0&limit=100
 * 
 * rsss feed:
 * 192.168.2.10:9117/api/v2.0/indexers/newpct/results/torznab/api?apikey=k1ryk9av87gxjk9e9sj7kpka5mommaxc&t=search&cat=&q=
 * 
 * get capat
 * http://192.168.2.10:9117/api/v2.0/indexers/newpct/results/torznab/api?apikey=k1ryk9av87gxjk9e9sj7kpka5mommaxc&t=caps
 */
function torznab_get($indexer, $limit = null) {
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

function torznab_get_caps($indexer) {
    global $cfg;

    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/api?apikey=' . $cfg['jackett_key'] . '&t=caps';
    return curl_get_jackett($jackett_url);
}

function torznab_search_movies($words, $indexer, $limit = null) {
    global $cfg;

    if (empty($limit)) {
        $limit = $cfg['jacket_results'];
    }

    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/api?apikey=' . $cfg['jackett_key'] . '&t=search&extended=1&cat=2000&q=' . $words . '&limit=' . $limit;

    return curl_get_jackett($jackett_url);
}

function torznab_search_shows($words, $indexer, $limit = null) {
    global $cfg;

    if (empty($limit)) {
        $limit = $cfg['jacket_results'];
    }
    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/api?apikey=' . $cfg['jackett_key'] . '&t=search&extended=1&cat=5000&q=' . $words . '&limit=' . $limit;

    return curl_get_jackett($jackett_url);
}

function torznab_prep_movies($movies_results) {

    foreach ($movies_results as $indexer) {

        if (isset($indexer['channel']['item']['title'])) {
            $movie = $indexer['channel']['item'];
            isset($movie['files']) ? $files = $movie['files'] : $files = '';

            $torznab = $movie['torznab'];
            foreach ($torznab as $attr) {
                $movie[$attr['@attributes']['name']] = $attr['@attributes']['value'];
            }

            isset($movie['coverurl']) ? $poster = $movie['coverurl'] : $poster = '';

            $movies[] = [
                'id' => $movie['guid'],
                'title' => $movie['title'],
                'lang' => '',
                'release' => '',
                'size' => $movie['size'],
                'plot' => $movie['description'],
                'files' => $files,
                'download' => $movie['link'],
                'category' => $movie['category'],
                'source' => $movie['jackettindexer'],
                'poster' => $poster,
            ];
        } else if (isset($indexer['channel']['item'])) {
            foreach ($indexer['channel']['item'] as $movie) {
                isset($movie['files']) ? $files = $movie['files'] : $files = '';

                $torznab = $movie['torznab'];
                foreach ($torznab as $attr) {
                    $movie[$attr['@attributes']['name']] = $attr['@attributes']['value'];
                }
                isset($movie['coverurl']) ? $poster = $movie['coverurl'] : $poster = '';
                $movies[] = [
                    'id' => $movie['guid'],
                    'title' => $movie['title'],
                    'lang' => '',
                    'release' => '',
                    'size' => $movie['size'],
                    'plot' => $movie['description'],
                    'files' => $files,
                    'download' => $movie['link'],
                    'category' => $movie['category'],
                    'source' => $movie['jackettindexer'],
                    'poster' => $poster,
                ];
            }
        }
    }

    return $movies;
}

function torznab_prep_shows($shows_results) {

    $shows = [];
    foreach ($shows_results as $indexer) {

        if (isset($indexer['channel']['item']['title'])) {
            $show = $indexer['channel']['item'];
            isset($show['files']) ? $files = $show['files'] : $files = '';

            $torznab = $show['torznab'];
            foreach ($torznab as $attr) {
                $show[$attr['@attributes']['name']] = $attr['@attributes']['value'];
            }
            isset($show['coverurl']) ? $poster = $show['coverurl'] : $poster = '';
            $shows[] = [
                'id' => $show['guid'],
                'title' => $show['title'],
                'lang' => $show['language'],
                'release' => '',
                'size' => $show['size'],
                'plot' => $show['description'],
                'files' => $files,
                'download' => $show['link'],
                'category' => $show['category'],
                'source' => $show['jackettindexer'],
                'poster' => $poster,
            ];
        } else if (isset($indexer['channel']['item'])) {
            foreach ($indexer['channel']['item'] as $show) {
                isset($show['files']) ? $files = $show['files'] : $files = '';

                $torznab = $show['torznab'];
                foreach ($torznab as $attr) {
                    $show[$attr['@attributes']['name']] = $attr['@attributes']['value'];
                }
                isset($show['coverurl']) ? $poster = $show['coverurl'] : $poster = '';

                $shows[] = [
                    'id' => $show['guid'],
                    'title' => $show['title'],
                    'lang' => '',
                    'release' => '',
                    'size' => $show['size'],
                    'plot' => $show['description'],
                    'files' => $files,
                    'download' => $show['link'],
                    'category' => $show['category'],
                    'source' => $show['jackettindexer'],
                    'poster' => $poster,
                ];
            }
        }
    }

    return $shows;
}
