<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function search_media_torrents($media_type, $search, $head = null, $nohtml = false) {
    global $cfg, $log, $db;

    if ($media_type == 'movies') {
        $jackett_search_media_cache = 'jackett_search_movies_cache';
        $jackett_db = 'jackett_movies';
        $search_words = iconv($cfg['charset'], "ascii//TRANSLIT", $search['words']);
    } else if ($media_type == 'shows') {
        $jackett_search_media_cache = 'jackett_search_shows_cache';
        $jackett_db = 'jackett_shows';

        if (!empty($search['episode'])) {
            $search_words = iconv($cfg['charset'], "ascii//TRANSLIT", $search['words']) . ' ' . $search['episode'];
        } else {
            $search_words = iconv($cfg['charset'], "ascii//TRANSLIT", $search['words']);
        }
    } else {
        return false;
    }

    $result = [];
    $page = '';
    $results_count = 0;
    $cache_media_expire = 0;

    $search_words = strtolower($search_words);

    if ($cfg['search_cache']) {
        $media_cache_check = $db->getItemByField($jackett_search_media_cache, 'words', $search_words);
        !isset($media_cache_check['updated']) ? $media_cache_check['updated'] = 0 : null;

        if (time() > ($media_cache_check['updated'] + $cfg['search_cache_expire'])) {
            $expire = $media_cache_check['updated'] + $cfg['search_cache_expire'];
            $time_now = time();
            $log->debug("New: Media cache expire ($media_type), requesting $time_now:$expire");
            $cache_media_expire = 1;
        } else {
            //$log->debug("News:  Using media cache");
            $ids = explode(',', $media_cache_check['ids']);

            if (empty($ids) || count($ids) <= 0) {
                return false;
            }
            foreach ($ids as $cache_id) {
                //TODO: GET ALL IDS IN ONE QUERY
                $db_item = $db->getItemById($jackett_db, trim($cache_id));
                !empty($db_item) ? $media_db[] = $db_item : null;
            }
        }
    }

    if (!$cfg['search_cache'] || $cache_media_expire) {
        foreach ($cfg['jackett_indexers'] as $indexer) {
            $caps = jackett_get_caps($indexer);
            $categories = jackett_get_categories($caps['categories']['category']);

            ($media_type) == 'movies' ? $jackett_media_key = 'movie-search' : $jackett_media_key = 'tv-search';
            if ($caps['searching'][$jackett_media_key]['@attributes']['available'] == "yes") {
                //some indexer jackett return the query year added to title (hack for radarr) we query without year and filter later
                //update: query only title if request chapter, filter later too
                if ((preg_match('/\s+\d{4}/i', $search_words, $match) == 1) || (preg_match('/S\d{2}E\d{2}/i', $search_words, $match) == 1)) {
                    $words_clean = getFileTitle($search_words);
                    $indexer_results = jackett_search_media($media_type, $words_clean, $indexer, $categories);
                } else {
                    $indexer_results = jackett_search_media($media_type, $search_words, $indexer, $categories);
                }
                $result[$indexer] = $indexer_results;
            }
            isset($result[$indexer]['channel']['item']) ? $results_count = count($result[$indexer]['channel']['item']) + $results_count : null;
        }

        if ($results_count <= 0) {
            return false;
        }

        $media_db = jackett_prep_media($media_type, $result);
        if (($cfg['search_cache'] && $cache_media_expire)) {
            $media_cache['words'] = $search_words;
            $media_cache['updated'] = time();
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
    $topt['view_type'] = $media_type . '_torrent';
    if (empty($media_db)) {
        return false;
    }

    if (preg_match('/\s+\d{4}/i', $search_words, $match) == 1) {
        $year = trim($match[0]);

        foreach ($media_db as $item_key => $item) {
            $item_title = $item['title'];
            if (!(strpos($item_title, $year))) {
                unset($media_db[$item_key]);
            }
        }
    }
    if (preg_match('/S\d{2}E\d{2}/i', $search_words, $match) == 1) {
        $episode = trim($match[0]);

        foreach ($media_db as $item_key => $item) {
            $item_title = $item['title'];
            if (!(stripos($item_title, $episode))) {
                unset($media_db[$item_key]);
            }
        }
    }
    if (!valid_array($media_db)) {
        return false;
    }
    if ($nohtml) {
        return $media_db;
    }

    $media_db = mix_media_res($media_db);
    $page .= buildTable($head, $media_db, $topt);

    return $page;
}

function jackett_search_media($media_type, $words, $indexer, $categories, $limit = null) {
    global $cfg, $log, $LNG;

    $starttime = getPerfTime();
    empty($limit) ? $limit = $cfg['jackett_results'] : null;
    $disable_time = !empty($cfg['indexer_disable_time']) ? $cfg['indexer_disable_time'] : 24 * 60 * 60;

    if (($indexer_disable = getPrefsItem($indexer . '_disable', true))) {
        if ($indexer_disable != '0' && $indexer_disable > time()) {
            return false;
        } else if ($indexer_disable != '0') {
            setPrefsItem($indexer . '_disable', '0', true);
        }
    }
    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api_path'] . '/indexers/' . $indexer . '/results/torznab/';
    $words = rawurlencode($words);

    //Movie cat begin 2 or show 5
    ($media_type == 'movies') ? $jkt_cat_first_digit = 2 : $jkt_cat_first_digit = 5;

    foreach ($categories as $category) {
        if (substr($category, 0, 1) == $jkt_cat_first_digit) {
            isset($cats) ? $cats .= ',' . $category : $cats = $category;
        }
    }
    if (empty($cats)) {
        return false;
    }
    $params = 'api?apikey=' . $cfg['jackett_key'] . '&t=search&extended=1&cat=' . $cats . '&q=' . $words . '&limit=' . $limit;

    $result = curl_get_jackett($jackett_url, $params);

    $timediff = getPerfTime() - $starttime;
    if (formatPerfTime($timediff) > $cfg['slow_flow']) {
        $log->addStateMsg("[{$LNG['L_NOTICE']}] $indexer  {$LNG['L_SLOW_THE_FLOW']} " . formatPerfTime($timediff) . " {$LNG['L_SECONDS']}");
    }
    if (formatPerfTime($timediff) > 99) {
        setPrefsItem($indexer . '_disable', time() + $disable_time, true);
        $log->addStateMsg("[{$LNG['L_NOTICE']}]: $indexer {$LNG['L_DISABLED']} $disable_time {$LNG['L_SECONDS']}");
    }

    return $result;
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

            $media[] = [
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
                'clean_title' => clean_title($item['title'])
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
                $media[] = [
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
                    'clean_title' => clean_title($item['title'])
                ];
            }
        }
    }

    if (valid_array($media)) {
        $media = array_reverse($media);
        //Get all item guid results
        foreach ($media as $item) {
            !isset($guids) ? $guids = $item['guid'] : $guids .= ',' . $item['guid'];
        }
        //Select from db  all items with same guids
        $items_id_guid = $db->selectMultiple($jackett_db, 'guid', $guids, 'id,guid,guessed_poster,guessed_trailer');

        //media haven't id and we need it, here we check if item guid exist in the database
        //if item exists we add the id to media id
        //if item not exist we additem, get the last id insert and add to the media item id the id
        foreach ($media as $key => $item) {
            $found = 0;
            foreach ($items_id_guid as $item_id_guid) {
                if ($item_id_guid['guid'] == $item['guid']) {
                    $found = 1;
                    $media[$key]['id'] = $item_id_guid['id'];
                    //we need this too
                    $media[$key]['guessed_poster'] = $item_id_guid['guessed_poster'];
                    $media[$key]['guessed_trailer'] = $item_id_guid['guessed_trailer'];
                    break;
                }
            }
            if (!$found) {
                $last_id = $db->addItem($jackett_db, $item);
                $media[$key]['id'] = $last_id;
            }
        }
        /* OLD
          $db->addItemsUniqField($jackett_db, $media, 'guid');

          foreach ($media as $key => $item) {
          $id = $db->getIdByField($jackett_db, 'guid', $item['guid']);
          $media[$key]['id'] = $id;
          }
         *
         */
    } else {
        return false;
    }

    return $media;
}

function jackett_get($indexer, $limit = null) {
    global $cfg;

    (empty($limit)) ? $limit = $cfg['jackett_results'] : null;

    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api_path'] . '/indexers/' . $indexer . '/results/torznab/';
    $params = 'api?t=search&extended=1&apikey=' . $cfg['jackett_key'] . '&limit=' . $limit;

    return curl_get_jackett($jackett_url, $params);
}

function jackett_get_caps($indexer) {
    global $cfg;
    $params = '';

    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api_path'] . '/indexers/' . $indexer . '/results/torznab/';
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
                if (isset($subcat['@attributes']['id'])) {
                    $cats[] = $subcat['@attributes']['id'];
                } else if (isset($subcat['id'])) {
                    $cats[] = $subcat['id'];
                }
            }
        }
    }

    return $cats;
}
