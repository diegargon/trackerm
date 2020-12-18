<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego/@/envigo.net)
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

        if ((time() > ($media_cache_check['updated'] + $cfg['search_cache_expire']))) {
            $log->debug("News: $media_type cache expire, Requesting");
            $cache_media_expire = 1;
        } else {
            $log->debug("News: $media_type using cache " . ( ($media_cache_check['updated'] + $cfg['search_cache_expire']) - time()));
            $ids = explode(',', $media_cache_check['ids']);
            if (empty($ids) || count($ids) <= 0) {
                return false;
            }
            foreach ($ids as $cache_id) {
                $res_media_db[] = $db->getItemById($jackett_db, trim($cache_id));
            }
        }
    }

    if (!$cfg['search_cache'] || $cache_media_expire) {
        foreach ($cfg['jackett_indexers'] as $indexer) {
            $caps = jackett_get_caps($indexer);
            $categories = jackett_get_categories($caps['categories']['category']);

            if ($cache_media_expire) {
                $results = '';
                $results = jackett_search_media($media_type, '', $indexer, $categories);
                $results ? $media_res[$indexer] = $results : null;
            }
        }
    }

    ($cache_media_expire == 1) || !$cfg['search_cache'] ? $res_media_db = jackett_prep_media($media_type, $media_res) : null;

    /* BUILD PAGE */
    $page_news = '';

    if (!empty($res_media_db)) {
        $topt['search_type'] = $media_type;
        ($media_type == 'movies') ? $head = 'L_MOVIES' : $head = 'L_SHOWS';
        $res_media_db = mix_media_res($res_media_db);
        $page_news_media = buildTable($head, $res_media_db, $topt);
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
    $indexers = [];
    $media = [];

    foreach ($res_media_db as $item) {
        $indexers[$item['source']][] = $item;
    }
    $max_items_by_indexer = 0;
    $total_indexers = count($indexers);
    $indexers_names = [];
    foreach ($indexers as $key => $indexer) {
        $indexers_names[] = $key;
        $total_indexer = count($indexer);
        if ($total_indexer > $max_items_by_indexer) {
            $max_items_by_indexer = $total_indexer;
        }
    }
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

    //testing func: must break when all arrays are empty, for safe while testing break after 10000 too
    //That walk throught indexers arrays for mix results, and if in the same indexers (next entry) the  result have same title
    //its added too, if not change to another indexer for mix results.
    //Probably must be a better way of doing this. And probably tomorrow i don't
    //known how works this messy... and is better rewrite again than found a maybe in the future bug.

    while ($i != 10000) {
        if (isset($indexers[$indexers_names[$indexer_pointer]][0])) {
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



    //return $res_media_db;
    return $media;
}
