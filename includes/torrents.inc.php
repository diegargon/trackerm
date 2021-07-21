<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
function torrents_filters(&$results) {
    //Ignore words
    torrents_filter_words($results);
    //Ignore_size
    torrents_filter_size($results);
    //Selected indexer
    torrents_filter_indexers($results);
    //Only freelech
    torrents_filter_only_freelech($results);
}

function torrents_filter_words(&$torrent_results) {
    global $prefs;

    if (!empty($prefs->getPrefsItem('new_ignore_words_enable')) && !empty($prefs->getPrefsItem('new_ignore_keywords'))) {
        $ignore_keywords = array_map('trim', explode(',', $prefs->getPrefsItem('new_ignore_keywords')));
        foreach ($torrent_results as $key => $item) {
            $match = str_ireplace($ignore_keywords, '', $item['title']);
            if (trim($match) != trim($item['title'])) {
                unset($torrent_results[$key]);
            }
        }
    }
}

function torrents_filter_size(&$torrent_results) {
    global $prefs;

    if (!empty($prefs->getPrefsItem('new_ignore_size_enable')) && !empty($prefs->getPrefsItem('new_ignore_size'))) {
        foreach ($torrent_results as $key => $item) {
            $gbytes = bytesToGB($item['size'], 2);
            if ($gbytes > trim($prefs->getPrefsItem('new_ignore_size'))) {
                unset($torrent_results[$key]);
            }
        }
    }
}

function torrents_filter_indexers(&$torrent_results) {
    global $prefs;

    $sel_indexer = $prefs->getPrefsItem('sel_indexer');

    if (!empty($sel_indexer && $sel_indexer != 'sel_indexer_none')) {
        foreach ($torrent_results as $key_item => $item) {
            if (strtolower($sel_indexer) !== strtolower($item['source'])) {
                unset($torrent_results[$key_item]);
            }
        }
    }
}

function torrents_filter_only_freelech(&$torrent_results) {
    global $prefs;

    $only_freelech = $prefs->getPrefsItem('only_freelech');

    if (!empty($only_freelech)) {
        foreach ($torrent_results as $key_item => $item) {
            if ($item['freelech'] != 1) {
                unset($torrent_results[$key_item]);
            }
        }
    }
}

function where_filters() {
    global $prefs;

    $where = '';
    if ($prefs->getPrefsItem('only_freelech')) {
        !empty($where) ? $where .= ' AND' : $where .= 'WHERE ';
        $where .= ' freelech = 1 ';
    }


    $sel_indexer = $prefs->getPrefsItem('sel_indexer');
    if (!empty($sel_indexer && $sel_indexer != 'sel_indexer_none')) {
        !empty($where) ? $where .= ' AND' : $where .= 'WHERE ';
        $where .= " source LIKE '%$sel_indexer%'";
    }
    if (!empty($prefs->getPrefsItem('new_ignore_size_enable')) && !empty($prefs->getPrefsItem('new_ignore_size'))) {
        !empty($where) ? $where .= ' AND' : $where .= 'WHERE ';
        $bytes = GBTobytes($prefs->getPrefsItem('new_ignore_size'));
        $where .= " size < $bytes ";
    }

    $keywords = $prefs->getPrefsItem('new_ignore_keywords');
    if (!empty($keywords) && $prefs->getPrefsItem('new_ignore_words_enable')) {
        $ignore_keywords = array_map('trim', explode(',', $prefs->getPrefsItem('new_ignore_keywords')));
        foreach ($ignore_keywords as $ignore_keyword) {
            !empty($where) ? $where .= ' AND' : $where .= 'WHERE ';
            $where .= " title  NOT LIKE '%$ignore_keyword%' ";
        }
    }

    return $where;
}

function show_cached_torrents($media_type) {
    global $prefs, $db;

    $npage = Filter::getInt('npage');
    $search_type = Filter::getString('search_type');
    $topt = [];

    empty($npage) || (!empty($search_type) && $search_type !== $media_type) ? $npage = 1 : null;

    if ($media_type == 'movies') {
        $jackett_db = 'jackett_movies';
    } else {
        $jackett_db = 'jackett_shows';
    }

    $rows = $prefs->getPrefsItem('tresults_rows');
    $columns = $prefs->getPrefsItem('tresults_columns');

    $n_results = $rows * $columns;
    $npage == 1 ? $end = $n_results : $end = $npage * $n_results;
    $npage == 1 ? $start = 0 : $start = ($npage - 1) * $n_results;

    $where = where_filters();
    $topt['num_table_rows'] = $db->qSingle("SELECT COUNT(*) FROM $jackett_db $where");
    $query = "SELECT * FROM $jackett_db $where ORDER BY id DESC LIMIT $start,$end ";

    $results = $db->query($query);
    $media = $db->fetchAll($results);
    if (!valid_array($media)) {
        return false;
    }
    $topt['view_type'] = $media_type . '_torrent';
    $topt['search_type'] = $media_type;

    return buildTable('L_' . strtoupper($media_type), $media, $topt);
}
