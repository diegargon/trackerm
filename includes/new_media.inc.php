<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function page_new_media(string $media_type) {
    global $cfg, $db, $LNG, $log, $prefs;

    $templates = [];
    $templates['templates'] = [];

    $search_type = Filter::getString('search_type');
    $columns = $prefs->getPrefsItem('tresults_columns');
    $rows = $prefs->getPrefsItem('tresults_rows');
    $items_per_page = $columns * $rows;
    $page = Filter::getString('page');
    $npage = Filter::getInt('npage');

    empty($npage) || (!empty($search_type) && $search_type !== $media_type) ? $npage = 1 : null;
    $cache_media_expire = 0;
    $topt = [];

    if (!empty($_POST['search_keyword'])) {
        $search_keyword = Filter::postString('search_keyword');
    } else if (!empty($_GET['search_keyword'])) {
        $search_keyword = Filter::getString('search_keyword');
    }
    !empty($search_keyword) ? $topt['search_keyword'] = $search_keyword : null;
    if ($media_type == 'movies') {
        $search_cache_db = 'jackett_search_movies_cache';
        $jackett_db = 'jackett_movies';
    } else {
        $search_cache_db = 'jackett_search_shows_cache';
        $jackett_db = 'jackett_shows';
    }

    if ($cfg['search_cache']) {
        $where['words'] = $media_cache_check = $db->getItemByField($search_cache_db, 'words', '');
        if (!valid_array($media_cache_check)) {
            $media_cache_check = [];
        }
        if (!isset($media_cache_check['updated'])) {
            $media_cache_check['updated'] = 0;
        }

        if ((time() > ($media_cache_check['updated'] + $cfg['new_cache_expire']))) {
            $log->debug("New media: $media_type cache expire, Requesting Now:" . time() . " Expire:" . ($media_cache_check['updated'] + $cfg['new_cache_expire']));
            $cache_media_expire = 1;
        } else {
            //$log->debug("News: $media_type using cache " . ( ($media_cache_check['updated'] + $cfg['new_cache_expire']) - time()));
            if (!empty($media_cache_check['ids'])) {
                $ids = $media_cache_check['ids'];
                $res_media_db = $db->selectMultiple($jackett_db, 'id', $ids);
            } else {
                //No results try without cache
                $cache_media_expire = 1;
            }
        }
    }

    if (!$cfg['search_cache'] || $cache_media_expire) {
        foreach ($cfg['jackett_indexers'] as $indexer) {
            $caps = jackett_get_caps($indexer);
            if (!valid_array($caps)) {
                continue;
            }
            $categories = jackett_get_categories($caps['categories']['category']);
            $results = '';
            $results = jackett_search_media($media_type, '', $indexer, $categories);
            $results ? $media_res[$indexer] = $results : null;
        }
    }

    ($cache_media_expire == 1) || !$cfg['search_cache'] && !empty($media_res) ? $res_media_db = jackett_prep_media($media_type, $media_res) : null;

    if (!empty($search_keyword) && !empty($res_media_db)) {
        $res_tmp = [];
        foreach ($res_media_db as $res) {
            if (stripos($res['title'], strtolower($search_keyword)) !== false) {
                $res_tmp[] = $res;
            }
        }
        $res_media_db = $res_tmp;
    }

    if (empty($res_media_db)) {
        $templates['templates'][] = [
            'name' => 'msgbox',
            'tpl_file' => 'msgbox',
            'tpl_pri' => 5,
            'tpl_place' => $media_type . '_torrents',
            'tpl_place_var' => 'items',
            'tpl_vars' => ['title' => $LNG['L_' . strtoupper($media_type)], 'body' => $LNG['L_NO_RESULTS']]
        ];
        return $templates;
    }

    usort($res_media_db, function ($a, $b) {
        return $b['id'] - $a['id'];
    });

    //UPDATE CACHE. save before doing more things
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

    //Filter words, size indexer freelech
    torrents_filters($res_media_db);

    /* BUILD PAGE */

    if (!empty($res_media_db)) {
        $topt['media_type'] = $media_type;
        $topt['view_type'] = $media_type . '_torrent';
        ($media_type == 'movies') ? $topt['head'] = $LNG['L_MOVIES'] : $topt['head'] = $LNG['L_SHOWS'];

        $res_media_db = mix_media_res($res_media_db);
        $topt['num_table_objs'] = count($res_media_db);
        if ($search_type == $media_type) {
            $jump_x_entrys = ($items_per_page * $npage) - $items_per_page;
        } else {
            $jump_x_entrys = 0;
        }

        $res_media_db = array_slice($res_media_db, $jump_x_entrys, $items_per_page);

        foreach ($res_media_db as &$res_media) {
            $res_media['poster'] = get_poster($res_media);
            $res_media['size'] = bytesToGB($res_media['size'], 2) . 'GB';
        }
        $pager_opts['npage'] = $npage;
        $pager_opts['page'] = $page;
        $pager_opts['pager_place'] = $media_type . '_torrents';
        $pager_opts['link_options'] = ['search_type' => $media_type];

        $templates['templates'][] = get_pager(array_merge($topt, $pager_opts));
        //$page_news .= $page_news_media;
    } else {
        $templates['templates'][] = [
            'name' => 'msgbox',
            'tpl_file' => 'msgbox',
            'tpl_pri' => 5,
            'tpl_place' => $media_type . '_torrents',
            'tpl_place_var' => 'items',
            'tpl_vars' => ['title' => $LNG['L_' . strtoupper($media_type)], 'body' => $LNG['L_NO_RESULTS']]
        ];
        return $templates;
    }


    $page_news = [
        'name' => 'items_torrent_' . $media_type,
        'tpl_file' => 'items_table',
        'tpl_pri' => 5,
        'tpl_place' => $media_type . '_torrents',
        'tpl_place_var' => 'items',
    ];

    $page_news['tpl_vars'] = $res_media_db;
    $topt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');
    $page_news['tpl_common_vars'] = $topt;

    $templates['templates'][] = $page_news;

    return $templates;
}

function mix_media_res(array $res_media_db) {
    $indexers = [];
    $media = [];

    if (empty($res_media_db) && count($res_media_db <= 1)) {
        return $res_media_db;
    }

    foreach ($res_media_db as $item) {
        $indexers[$item['source']][] = $item;
    }

    $indexers_names = array_keys($indexers);

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
    //This walk throught indexers arrays for mix results, get one from each indexer except  if in the same indexer the next entry
    // have same title, in this case  added too, if not change to another indexer for mix results.
    //Probably must be a better way of doing this. And probably tomorrow i don't
    //known how works this messy... and is better rewrite again this than found a maybe future bug.
    //One problem its if indexer X not update his media in some time his results will appears in the first results when is old release.
    //a solution can be check if the id of other indexers candidates are greater, if are greater jump the indexer with old results.
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
