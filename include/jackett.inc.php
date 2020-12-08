<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function search_media_torrents($media_type, $words, $head = null, $nohtml = false) {
    global $cfg, $log, $db;

    if ($media_type == 'movies') {
        $jackett_search_media_cache = 'jackett_search_movies_cache';
        $jackett_db = 'jackett_movies';
    } else if ($media_type == 'shows') {
        $jackett_search_media_cache = 'jackett_search_shows_cache';
        $jackett_db = 'jackett_shows';
    } else {
        return false;
    }

    $result = [];
    $page = '';
    $results_count = 0;
    $cache_media_expire = 0;

    if ($cfg['search_cache']) {
        $media_cache_check = $db->getItemByField($jackett_search_media_cache, 'words', $words);
        !isset($media_cache_check['update']) ? $media_cache_check['update'] = 0 : null;

        if (time() > ($media_cache_check['update'] + $cfg['search_cache_expire'])) {
            $log->debug("News: Media cache expire, Requesting");
            $cache_media_expire = 1;
        } else {
            //$log->debug("News:  Using media cache");
            $ids = explode(',', $media_cache_check['ids']);

            if (empty($ids) || count($ids) <= 0) {
                return false;
            }
            foreach ($ids as $cache_id) {
                $media_db[] = $db->getItemById($jackett_db, trim($cache_id));
            }
        }
    }

    if (!$cfg['search_cache'] || $cache_media_expire) {
        foreach ($cfg['jackett_indexers'] as $indexer) {
            $caps = jackett_get_caps($indexer);
            $categories = jackett_get_categories($caps['categories']['category']);

            ($media_type) == 'movies' ? $jackett_media_key = 'movie-search' : $jackett_media_key = 'tv-search';
            if ($caps['searching'][$jackett_media_key]['@attributes']['available'] == "yes") {
                $result[$indexer] = jackett_search_media($media_type, $words, $indexer, $categories);
            }
            isset($result[$indexer]['channel']['item']) ? $results_count = count($result[$indexer]['channel']['item']) + $results_count : null;
        }

        if ($results_count <= 0) {
            return false;
        }

        $media_db = jackett_prep_media($media_type, $result);
        if (($cfg['search_cache'] && $cache_media_expire)) {
            $media_cache['words'] = $words;
            $media_cache['update'] = time();
            $media_cache['ids'] = '';

            $last_element = end($media_db);
            foreach ($media_db as $tocache_media) {
                $media_cache['ids'] .= $tocache_media['id'];
                $tocache_media['id'] != $last_element['id'] ? $media_cache['ids'] .= ', ' : null;
            }
            $db->upsertItemByField($jackett_search_media_cache, $media_cache, 'words');
        }
    }

    $topt['search_type'] = $media_type;
    if ($nohtml) {
        return $media_db;
    }

    $page .= buildTable($head, $media_db, $topt);

    return $page;
}

function jackett_search_media($media_type, $words, $indexer, $categories, $limit = null) {
    global $cfg;

    empty($limit) ? $limit = $cfg['jackett_results'] : null;

    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/';
    $words = rawurlencode($words);

    ($media_type == 'movies') ? $jkt_cat_first_digit = 2 : $jkt_cat_first_digit = 5;

    foreach ($categories as $category) {
        if (substr($category, 0, 1) == $jkt_cat_first_digit) {
            isset($cats) ? $cats .= ',' . $category : $cats = $category;
        }
    }

    $params = 'api?apikey=' . $cfg['jackett_key'] . '&t=search&extended=1&cat=' . $cats . '&q=' . $words . '&limit=' . $limit;

    return curl_get_jackett($jackett_url, $params);
}

function jackett_prep_media($media_type, $media_results) {
    global $db;

    $jackett_db = 'jackett_' . $media_type;

    $media = [];
    foreach ($media_results as $indexer) {
        //One item
        if (isset($indexer['channel']['item']['title'])) {
            $item = $indexer['channel']['item'];
            isset($item['files']) ? $files = $item['files'] : $files = '';

            $torznab = $item['torznab'];
            foreach ($torznab as $attr) {
                $item[$attr['@attributes']['name']] = $attr['@attributes']['value'];
            }
            isset($item['coverurl']) ? $poster = $item['coverurl'] : $poster = '';
            !empty($item['description']) ? $description = $item['description'] : $description = '';

            ($media_type == 'movies') ? $ilink = 'movies_torrent' : $ilink = 'shows_torrent';
            $media[] = [
                'ilink' => $ilink,
                'guid' => $item['guid'],
                'title' => $item['title'],
                'release' => $item['pubDate'],
                'size' => $item['size'],
                'plot' => $description,
                'files' => $files,
                'download' => $item['link'],
                'category' => $item['category'],
                'source' => $item['jackettindexer'],
                'poster' => $poster,
            ];
            //More than one
        } else if (isset($indexer['channel']['item'])) {
            foreach ($indexer['channel']['item'] as $item) {
                isset($item['files']) ? $files = $item['files'] : $files = '';

                $torznab = $item['torznab'];
                foreach ($torznab as $attr) {
                    $item[$attr['@attributes']['name']] = $attr['@attributes']['value'];
                }
                !empty($item['coverurl']) ? $poster = $item['coverurl'] : $poster = '';
                !empty($item['description']) ? $description = $item['description'] : $description = '';
                ($media_type == 'movies') ? $ilink = 'movies_torrent' : $ilink = 'shows_torrent';
                $media[] = [
                    'ilink' => $ilink,
                    'guid' => $item['guid'],
                    'title' => $item['title'],
                    'release' => $item['pubDate'],
                    'size' => $item['size'],
                    'plot' => $description,
                    'files' => $files,
                    'download' => $item['link'],
                    'category' => $item['category'],
                    'source' => $item['jackettindexer'],
                    'poster' => $poster,
                ];
            }
        }
    }

    if (!empty($media) && count($media) > 0) {
        $db->addItemsUniqField($jackett_db, $media, 'guid');
        //add ids
        foreach ($media as $key => $item) {
            $id = $db->getIdByField($jackett_db, 'guid', $item['guid']);
            $media[$key]['id'] = $id;
        }
    }

    return $media;
}

function jackett_get($indexer, $limit = null) {
    global $cfg;

    (empty($limit)) ? $limit = $cfg['jackett_results'] : null;

    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/';
    $params = 'api?t=search&extended=1&apikey=' . $cfg['jackett_key'] . '&limit=' . $limit;

    return curl_get_jackett($jackett_url, $params);
}

function jackett_get_caps($indexer) {
    global $cfg;
    $params = '';

    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api'] . '/indexers/' . $indexer . '/results/torznab/';
    $params = 'api?apikey=' . $cfg['jackett_key'] . '&t=caps';
    return curl_get_jackett($jackett_url, $params);
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
