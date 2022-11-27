<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function search_media_torrents($media_type, $search) {
    global $cfg, $log, $db;

    $result = [];
    $results_count = 0;
    $cache_media_expire = 0;

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

    if ($cfg['search_cache']) {
        $media_cache_check = $db->getItemByField($jackett_search_media_cache, 'words', $search_words);
        !isset($media_cache_check['updated']) ? $media_cache_check['updated'] = 0 : null;

        $expire = $media_cache_check['updated'] + $cfg['search_cache_expire'];

        if (time() > $expire) {
            $time_now = time();
            $log->debug("New: Media cache expire ($media_type), requesting $time_now:$expire");
            $cache_media_expire = 1;
        } else {
            $media_db = $db->getItemsByIds($jackett_db, $media_cache_check['ids']);
        }
    }

    if (!$cfg['search_cache'] || $cache_media_expire) {
        foreach ($cfg['jackett_indexers'] as $indexer) {
            $caps = jackett_get_caps($indexer);
            if (!valid_array($caps) || empty($caps['categories']['category'])) {
                continue;
            }
            $categories = jackett_get_categories($caps['categories']['category']);

            ($media_type) == 'movies' ? $jackett_media_key = 'movie-search' : $jackett_media_key = 'tv-search';
            if ($caps['searching'][$jackett_media_key]['@attributes']['available'] == "yes") {
                /*
                 * We can't search with the year added since Jackett return in some indexers the searched year added to every result
                 * If the search contains the year we query without year and then filter later
                 * We do the same with SXXEXX
                 */
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
        if (empty($media_db)) {
            return false;
        }
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

    //FIX  too many foreach. haz el foreach y luego dependiendo lo que quieras a cada item.

    /* Here is where if the search contains the year we delete results without year */
    if (preg_match('/\s+\d{4}/i', $search_words, $match) == 1) {
        $year = trim($match[0]);

        foreach ($media_db as $item_key => $item) {
            $item_title = $item['title'];
            if (!(strpos($item_title, $year))) {
                unset($media_db[$item_key]);
            }
        }
    }
    /* Here is where if the search contains the SxxExx we delete results without year */
    if (preg_match('/S\d{2}E\d{2}/i', $search_words, $match) == 1) {
        $episode = trim($match[0]);
        foreach ($media_db as $item_key => $item) {
            $item_title = $item['title'];
            if (!(stripos($item_title, $episode))) {
                unset($media_db[$item_key]);
            }
        }
    }
    /* Check again after the unsets */
    if (!valid_array($media_db)) {
        return false;
    }

    foreach ($media_db as &$item) {
        $item['size'] = bytesToGB($item['size'], 2) . 'GB';
        $item['poster'] = get_poster($item);
    }
    return $media_db;
}

function jackett_search_media($media_type, $words, $indexer, $categories, $limit = null) {
    global $cfg, $log, $LNG, $prefs;

    $starttime = getPerfTime();
    empty($limit) ? $limit = $cfg['jackett_results'] : null;
    $disable_time = !empty($cfg['indexer_disable_time']) ? $cfg['indexer_disable_time'] : 6 * 60 * 60;

    if (($indexer_disable = $prefs->getPrefsItem($indexer . '_disable', true))) {
        if ($indexer_disable != '0' && $indexer_disable > time()) {
            return false;
        } else if ($indexer_disable != '0') {
            $prefs->setPrefsItem($indexer . '_disable', '0', true);
        }
    }
    $jackett_url = $cfg['jackett_srv'] . $cfg['jackett_api_path'] . '/indexers/' . $indexer . '/results/torznab/';
    $words = rawurlencode($words);

    //Movies category begin 2 and shows 5
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
    if (formatPerfTime($timediff) > $cfg['curl_timeout'] - 1) {
        $prefs->setPrefsItem($indexer . '_disable', time() + $disable_time, true);
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
            if (isset($item['downloadvolumefactor']) && $item['downloadvolumefactor'] >= 1) {
                $freelech = 0;
            } else {
                $freelech = 1;
            }
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
                'freelech' => $freelech,
                'poster' => $poster,
                'clean_title' => clean_title($item['title'])
            ];
            //More than one
        } else if (isset($indexer['channel']['item'])) {
            $added_guids = []; //check duplicates in results, some problems in some indexers
            foreach ($indexer['channel']['item'] as $item) {
                if (in_array($item['guid'], $added_guids)) {
                    continue;
                }
                isset($item['files']) ? $files = $item['files'] : $files = '';

                $torznab = $item['torznab'];
                foreach ($torznab as $attr) {
                    $item[$attr['@attributes']['name']] = $attr['@attributes']['value'];
                }
                !empty($item['coverurl']) ? $poster = $item['coverurl'] : $poster = '';
                if (isset($item['downloadvolumefactor']) && $item['downloadvolumefactor'] >= 1) {
                    $freelech = 0;
                } else {
                    $freelech = 1;
                }
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
                    'freelech' => $freelech,
                    'poster' => $poster,
                    'clean_title' => clean_title($item['title'])
                ];
                $added_guids[] = $item['guid'];
            }
        }
    }

    if (!valid_array($media)) {
        return false;
    }

    $media = array_reverse($media);
    //Get all item guid and title results
    $titles = $guids = [];
    foreach ($media as $item) {
        $guids[] = $item['guid'];
        $titles[] = clean_title($item['title']);
    }

    //Get have in library based on torrents clean titles
    $titles = array_unique($titles);
    $library_master = 'library_master_' . $media_type;
    $media_have = $db->selectMultiple($library_master, 'clean_title', $titles, 'id, title');

    //Select from db all items with same guids
    $items_id_guid = $db->selectMultiple($jackett_db, 'guid', $guids, 'id,guid,guessed_poster,guessed_trailer,freelech, have_it');
    //media haven't id and we need it, here we check if item guid exist in the database
    //if item exists we add the id to media id
    //if item not exist we additem, get the last id insert and add to the media item id the id
    foreach ($media as $key => $item) {
        $found = 0;

        /* Mark as have it if have it */
        $item['have_it'] = 0;
        if (valid_array($media_have)) {
            foreach ($media_have as $item_have) {
                if (clean_title($item_have['title']) == clean_title($item['title'])) {
                    $item['have_it'] = $item_have['id'];
                    break;
                }
            }
        }
        foreach ($items_id_guid as $item_id_guid) {
            if (strcmp($item_id_guid['guid'], $item['guid']) === 0) {
                $found = 1;
                $media[$key]['id'] = $item_id_guid['id'];
                !empty($item_id_guid['guessed_poster']) ? $media[$key]['guessed_poster'] = $item_id_guid['guessed_poster'] : $media[$key]['guessed_poster'] = '';
                !empty($item_id_guid['guessed_trailer']) ? $media[$key]['guessed_trailer'] = $item_id_guid['guessed_trailer'] : $media[$key]['guessed_trailer'] = '';
                //If change the freelech status  must change in the db
                $update = [];
                if ($item_id_guid['freelech'] != $media[$key]['freelech']) {
                    $update['freelech'] = $media[$key]['freelech'];
                }
                //if have_it change and item exist in db must change in the db
                if ($item_id_guid['have_it'] != $item['have_it']) {
                    $update['have_it'] = $item['have_it'];
                }
                if (valid_array($update)) {
                    $db->updateItemById($jackett_db, $item_id_guid['id'], $update);
                }
                break;
            }
        }
        if (empty($found)) {
            jackett_guess_fields($media_type, $item);
            !empty($item['guessed_poster']) ? $media[$key]['guessed_poster'] = $item['guessed_poster'] : $media[$key]['guessed_poster'] = '';
            !empty($item['guessed_trailer']) ? $media[$key]['guessed_trailer'] = $item['guessed_trailer'] : $media[$key]['guessed_trailer'] = '';
            $last_id = $db->addItem($jackett_db, $item);
            $media[$key]['id'] = $last_id;
        }
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
        if (isset($category['id'])) {
            $cats[] = $category['id'];
        }
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

function jackett_guess_fields($media_type, &$item) {

    if (empty($item['poster']) && empty($item['guessed_poster'])) {
        $item['guessed_poster'] = -1;
        $poster = mediadb_guessPoster($item['title'], $media_type);
        if (!empty($poster)) {
            $item['guessed_poster'] = $poster;
        }
    }

    if (empty($item['trailer']) && empty($item['guessed_trailer'])) {
        $trailer = mediadb_guessTrailer($item['title'], $media_type);
        if (!empty($trailer)) {
            if (substr($trailer, 0, 4) == 'http:') {
                $item['guessed_trailer'] = str_replace('http', 'https', $trailer);
            } else {
                $item['guessed_trailer'] = $trailer;
            }
        } else {
            $item['guessed_trailer'] = -1;
        }
    }
}
