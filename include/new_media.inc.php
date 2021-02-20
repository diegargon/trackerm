<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function page_new_media($media_type) {
    global $cfg, $db, $log;

    $cache_media_expire = 0;

    if ($media_type == 'movies') {
        $search_cache_db = 'jackett_search_movies_cache';
        $jackett_db = 'jackett_movies';
    } else {
        $search_cache_db = 'jackett_search_shows_cache';
        $jackett_db = 'jackett_shows';
    }

    if ($cfg['search_cache']) {
        $where['words'] = $media_cache_check = $db->getItemByField($search_cache_db, 'words', '');
        !isset($media_cache_check['updated']) ? $media_cache_check['updated'] = 0 : null;

        if ((time() > ($media_cache_check['updated'] + $cfg['new_cache_expire']))) {
            $log->debug("News: $media_type cache expire, Requesting");
            $cache_media_expire = 1;
        } else {
            //$log->debug("News: $media_type using cache " . ( ($media_cache_check['updated'] + $cfg['new_cache_expire']) - time()));
            if (!empty($media_cache_check['ids'])) {
                $ids = explode(',', $media_cache_check['ids']);
                if (empty($ids) || count($ids) <= 0) {
                    return false;
                }
                foreach ($ids as $cache_id) {
                    $res_media_db[] = $db->getItemById($jackett_db, trim($cache_id));
                }
            } else {
                //No results try without cache
                $cache_media_expire = 1;
            }
        }
    }

    if (!$cfg['search_cache'] || $cache_media_expire) {

        foreach ($cfg['jackett_indexers'] as $indexer) {
            $caps = jackett_get_caps($indexer);
            if (empty($caps) || count($caps) < 1) {
                return false;
            }
            $categories = jackett_get_categories($caps['categories']['category']);
            $results = '';
            $results = jackett_search_media($media_type, '', $indexer, $categories);
            $results ? $media_res[$indexer] = $results : null;
        }
    }

    ($cache_media_expire == 1) || !$cfg['search_cache'] && !empty($media_res) ? $res_media_db = jackett_prep_media($media_type, $media_res) : null;

    $final_res_media_db = $res_media_db; //res_ unfilter need to cache
    //ignore words
    if (!empty($cfg['new_ignore_words_enable']) && !empty($cfg['new_ignore_keywords'])) {
        $ignore_keywords = array_map('trim', explode(',', $cfg['new_ignore_keywords']));
        foreach ($final_res_media_db as $key => $item) {
            $match = str_ireplace($ignore_keywords, '', $item['title']);
            if (trim($match) != trim($item['title'])) {
                unset($final_res_media_db[$key]);
                //echo "Dropping by word " . $item['title'] . "<br>";
            }
        }
    }

    //ignore_size
    if (!empty($cfg['new_ignore_size_enable']) && !empty($cfg['new_ignore_size'])) {
        foreach ($final_res_media_db as $key => $item) {
            $gbytes = round(bytesToGB($item['size']), 2);
            if ($gbytes > trim($cfg['new_ignore_size'])) {
                unset($final_res_media_db[$key]);
                //echo "Dropping by size" . $item['title'] . ":$gbytes<br>";
            }
        }
    }
    /* BUILD PAGE */
    $page_news = '';

    if (!empty($final_res_media_db)) {
        $topt['search_type'] = $media_type;
        $topt['view_type'] = $media_type . '_torrent';
        ($media_type == 'movies') ? $head = 'L_MOVIES' : $head = 'L_SHOWS';
        $final_res_media_db = mix_media_res($final_res_media_db);
        $page_news_media = buildTable($head, $final_res_media_db, $topt);
        $page_news .= $page_news_media;
    }

    //UPDATE CACHE
    if (($cfg['search_cache'] && $cache_media_expire)) {
        $media_cache['words'] = '';
        $media_cache['updated'] = time();
        $media_cache['ids'] = '';
        $media_cache['media_type'] = $media_type;

        $last_element = end($res_media_db);
        foreach ($res_media_db as $tocache_media) {
            $media_cache['ids'] .= $tocache_media['id'];
            $tocache_media['id'] != $last_element['id'] ? $media_cache['ids'] .= ', ' : null;
        }

        $where['words'] = ['value' => ''];
        $where['media_type'] = ['value' => $media_type];
        $db->upsert($search_cache_db, $media_cache, $where);
    }

    return $page_news;
}

function mix_media_res($res_media_db) {
    global $cfg;

    $indexers = [];
    $media = [];

    if (empty($res_media_db) && count($res_media_db <= 1)) {
        return $res_media_db;
    }

    foreach ($res_media_db as $item) {
        $indexers[$item['source']][] = $item;
    }
    $max_items_by_indexer = 0;
    $indexers_names = [];
    foreach ($indexers as $name_key => $indexer) {
        if (empty($cfg['sel_indexer']) || strtolower($cfg['sel_indexer']) == strtolower($name_key) || $cfg['sel_indexer'] == 'sel_indexer_none') {
            $indexers_names[] = $name_key;
            $total_indexer = count($indexer);
            if ($total_indexer > $max_items_by_indexer) {
                $max_items_by_indexer = $total_indexer;
            }
        }
    }
    $total_indexers = count($indexers_names);
    /*  order two by indexer
      for ($i = 0; $i <= ($max_items_by_indexer) / 2; $i++) {
      foreach ($indexers as $indexer) {
      isset($indexer[$i]) ? $media[] = $indexer[$i] : null;
      isset($indexer[$i + 1]) ? $media[] = $indexer[$i + 1] : null;
      }
      }
     */

    $total_indexers = count($indexers);
    $last_item = null;
    $indexer_pointer = 0;
    $i = 0;

    //testing func
    //That walk throught indexers arrays for mix results, and if in the same indexers (next entry) the  result have same title
    //its added too, if not change to another indexer for mix results.
    //Probably must be a better way of doing this. And probably tomorrow i don't
    //known how works this messy... and is better rewrite again than found a maybe in the future bug.

    while (1) {
        if (isset($indexers_names[$indexer_pointer]) && isset($indexers[$indexers_names[$indexer_pointer]][0])) {
            $item = $indexers[$indexers_names[$indexer_pointer]][0];
            if (!isset($last_item['title'])) {
                $media[] = array_shift($indexers[$indexers_names[$indexer_pointer]]);
                $last_item = $item;
            } else if (isset($last_item['title']) && (getFileTitle($item['title']) == getFileTitle($last_item['title']))) {
                $media[] = array_shift($indexers[$indexers_names[$indexer_pointer]]);
                $last_item = $item;
            } else {
                $last_item = null;
                if ($indexer_pointer < ($total_indexers - 1)) {
                    $indexer_pointer++;
                } else {
                    $indexer_pointer = 0;
                }
            }
        } else {
            if ($indexer_pointer < ($total_indexers - 1)) {
                $indexer_pointer++;
            } else {
                $indexer_pointer = 0;
            }
        }

        $elements_rest = 0;
        foreach ($indexers_names as $indexer_name) {
            if (count($indexers[$indexer_name]) > 0) {
                $elements_rest = 1;
                break; //for
            }
        }
        if ($elements_rest == 0) {
            break; //while
        }
        $i++;
    }

    return $media;
}
