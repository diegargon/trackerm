<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function search_movie_torrents($words, $head = null, $nohtml = false) {
    global $cfg, $log, $db;

    $result = [];
    $page = '';
    $results_count = 0;
    $cache_movies_expire = 0;

    if ($cfg['search_cache']) {
        $movies_cache_check = $db->getItemById('jackett_search_movies', $words);
        if ((!$movies_cache_check) || (time() > ($movies_cache_check['cache_time'] + $cfg['search_cache_expire']))) {
            $log->debug("Movies search cache expire requesting");
            $cache_movies_expire = 1;
        } else {
            $movies_db = $movies_cache_check['results'];
            $log->debug("Movies seach cache expire in " . (time() - ($movies_cache_check['cache_time'] + $cfg['search_cache_expire'])));
            if (count($movies_db) <= 0) {
                return false;
            }
        }
    }

    if (!$cfg['search_cache'] || $cache_movies_expire) {
        foreach ($cfg['jackett_indexers'] as $indexer) {
            $caps = jackett_get_caps($indexer);
            $categories = jackett_get_categories($caps['categories']['category']);

            if ($caps['searching']['movie-search']['@attributes']['available'] == "yes") {
                $result[$indexer] = jackett_search_movies($words, $indexer, $categories);
            }
            isset($result[$indexer]['channel']['item']) ? $results_count = count($result[$indexer]['channel']['item']) + $results_count : null;
        }

        if ($results_count <= 0) {
            return false;
        }

        if (!$cfg['search_cache'] || $cache_movies_expire == 1) {
            $movies_db = jackett_prep_movies($result);
            $search_cache['search_keyword'] = $words;
            $search_cache['cache_time'] = time();
            $search_cache['results'] = $movies_db;
            $db->upsertElementById('jackett_search_movies', $words, $search_cache);
        }
    }

    $topt['search_type'] = 'movies';
    if ($nohtml) {
        return $movies_db;
    }

    $page .= buildTable($head, $movies_db, $topt);

    return $page;
}

function search_shows_torrents($words, $head = null, $nohtml = false) {
    global $cfg, $db, $log;

    $result = [];
    $page = '';
    $results_count = 0;
    $results_count = 0;
    $cache_shows_expire = 0;

    if ($cfg['search_cache']) {
        $shows_cache_check = $db->getItemById('jackett_search_shows', $words);
        if ((!$shows_cache_check) || (time() > ($shows_cache_check['cache_time'] + $cfg['search_cache_expire']))) {
            $log->debug("Shows search cache expire requesting");
            $cache_shows_expire = 1;
        } else {
            $log->debug("Shows search cache expire in " . (($shows_cache_check['cache_time'] + $cfg['search_cache_expire']) - time()));
            $shows_db = $shows_cache_check['results'];

            if (count($shows_db) <= 0) {
                return false;
            }
        }
    }

    if (!$cfg['search_cache'] || $cache_shows_expire) {
        foreach ($cfg['jackett_indexers'] as $indexer) {
            $caps = jackett_get_caps($indexer);
            $categories = jackett_get_categories($caps['categories']['category']);

            if ($caps['searching']['tv-search']['@attributes']['available'] == "yes") {
                $result[$indexer] = jackett_search_shows($words, $indexer, $categories);
            }
            isset($result[$indexer]['channel']['item']) ? $results_count = count($result[$indexer]['channel']['item']) + $results_count : null;
        }

        if ($results_count <= 0) {
            return false;
        }

        if (!$cfg['search_cache'] || $cache_shows_expire == 1) {
            $shows_db = jackett_prep_shows($result);
            $search_cache['search_keyword'] = $words;
            $search_cache['cache_time'] = time();
            $search_cache['results'] = $shows_db;
            $db->upsertElementById('jackett_search_shows', $words, $search_cache);
        }
    }
    $topt['search_type'] = 'shows';

    if ($nohtml) {
        return $shows_db;
    }
    $page .= buildTable($head, $shows_db, $topt);

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

    (empty($limit)) ? $limit = $cfg['jacket_results'] : null;

    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/';
    $params = 'api?t=search&extended=1&apikey=' . $cfg['jackett_key'] . '&limit=' . $limit;

    return curl_get_jackett($jackett_url, $params);
}

function jackett_get_caps($indexer) {
    global $cfg;
    $params = '';

    //$jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/api?apikey=' . $cfg['jackett_key'] . '&t=caps';
    //return curl_get_jackett($jackett_url, $params);
    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/';
    $params = 'api?apikey=' . $cfg['jackett_key'] . '&t=caps';
    return curl_get_jackett($jackett_url, $params);
}

function jackett_search_movies($words, $indexer, $categories, $limit = null) {
    global $cfg;

    empty($limit) ? $limit = $cfg['jacket_results'] : null;

    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/';
    $words = rawurlencode($words);

    foreach ($categories as $category) {
        if (substr($category, 0, 1) == 2) {
            isset($cats) ? $cats .= ',' . $category : $cats = $category;
        }
    }
    $params = 'api?apikey=' . $cfg['jackett_key'] . '&t=search&extended=1&cat=' . $cats . '&q=' . $words . '&limit=' . $limit;

    return curl_get_jackett($jackett_url, $params);
}

function jackett_search_shows($words, $indexer, $categories, $limit = null) {
    global $cfg;

    empty($limit) ? $limit = $cfg['jacket_results'] : null;

    //$jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/api?apikey=' . $cfg['jackett_key'] . '&t=search&extended=1&cat=5000&q=' . $words . '&limit=' . $limit;

    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/';
    $words = rawurlencode($words);

    foreach ($categories as $category) {
        if (substr($category, 0, 1) == 5) {
            isset($cats) ? $cats .= ',' . $category : $cats = $category;
        }
    }
    $params = 'api?apikey=' . $cfg['jackett_key'] . '&t=search&extended=1&cat=' . $cats . '&q=' . $words . '&limit=' . $limit;

    return curl_get_jackett($jackett_url, $params);
}

function jackett_prep_movies($movies_results) {
    global $db;

    $movies = [];
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
                'ilink' => 'movies_torrent',
                'guid' => $movie['guid'],
                'title' => $movie['title'],
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
                    'ilink' => 'movies_torrent',
                    'guid' => $movie['guid'],
                    'title' => $movie['title'],
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

    if (!empty($movies)) {

        $db->addUniqElements('jackett_movies', $movies, 'guid');

        //add ID's
        foreach ($movies as $key => $movie) {
            $id = $db->getIdByField('jackett_movies', 'guid', $movie['guid']);
            $movies[$key]['id'] = $id;
        }
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
                'ilink' => 'shows_torrent',
                'guid' => $show['guid'],
                'title' => $show['title'],
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
                    'ilink' => 'shows_torrent',
                    'guid' => $show['guid'],
                    'title' => $show['title'],
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

    if (!empty($shows)) {
        $db->addUniqElements('jackett_shows', $shows, 'guid');

        //add ID's
        foreach ($shows as $key => $show) {
            $id = $db->getIdByField('jackett_shows', 'guid', $show['guid']);
            $shows[$key]['id'] = $id;
        }
    }
    return $shows;
}

function jackett_get_categories($categories) {
    $cats = [];

    foreach ($categories as $category) {
        if (isset($category['@attributes'])) {
            if (isset($category['@attributes']['id'])) {
                $cats[] = $category['@attributes']['id'];
            }
        }
        if (isset($category['subcat'])) {
            foreach ($category['subcat'] as $subcat) {
                if (isset($subcat['@attributes'])) {
                    if (isset($subcat['@attributes']['id'])) {
                        $cats[] = $subcat['@attributes']['id'];
                    }
                }
            }
        }
    }

    return $cats;
}
