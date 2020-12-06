<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function page_new_media($media_type) {
    global $cfg, $db, $log;

    $cache_media_expire = 0;

    if ($media_type == 'movies') {
        $search_cache_db = 'jackett_search_movies_cache';
        $jacket_db = 'jackett_movies';
    } else {
        $search_cache_db = 'jackett_search_shows_cache';
        $jacket_db = 'jackett_shows';
    }

    if ($cfg['search_cache']) {
        $where['words'] = $media_cache_check = $db->getItemByField($search_cache_db, 'words', '');
        !isset($media_cache_check['update']) ? $media_cache_check['update'] = 0 : null;

        if ((time() > ($media_cache_check['update'] + $cfg['search_cache_expire']))) {
            $log->debug("News: $media_type cache expire, Requesting");
            $cache_media_expire = 1;
        } else {
            $log->debug("News: $media_type using cache " . ( ($media_cache_check['update'] + $cfg['search_cache_expire']) - time()));
            $ids = explode(',', $media_cache_check['ids']);
            if (empty($ids) || count($ids) <= 0) {
                return false;
            }
            foreach ($ids as $cache_id) {
                $res_media_db[] = $db->getItemById($jacket_db, trim($cache_id));
            }
        }
    }

    if (!$cfg['search_cache'] || $cache_media_expire) {
        foreach ($cfg['jackett_indexers'] as $indexer) {
            $caps = jackett_get_caps($indexer);
            $categories = jackett_get_categories($caps['categories']['category']);

            if ($cache_media_expire) {
                $results = '';
                if ($media_type == 'movies') {
                    $results = jackett_search_movies('', $indexer, $categories);
                } else if ($media_type == 'shows') {
                    $results = jackett_search_shows('', $indexer, $categories);
                }
                $results ? $media_res[$indexer] = $results : null;
            }
        }
    }

    if ($media_type == 'movies') {
        ($cache_media_expire == 1) || !$cfg['search_cache'] ? $res_media_db = jackett_prep_movies($media_res) : null;
    } else if ($media_type == 'shows') {
        ($cache_media_expire == 1) || !$cfg['search_cache'] ? $res_media_db = jackett_prep_shows($media_res) : null;
    }

    /* BUILD PAGE */
    $page_news = '';

    if (!empty($res_media_db)) {
        $topt['search_type'] = $media_type;
        ($media_type == 'movies') ? $head = 'L_MOVIES' : $head = 'L_SHOWS';
        $page_news_media = buildTable($head, $res_media_db, $topt);
        $page_news .= $page_news_media;
    }

    //UPDATE CACHE
    if (($cfg['search_cache'] && $cache_media_expire)) {
        $media_cache['words'] = '';
        $media_cache['update'] = time();
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
