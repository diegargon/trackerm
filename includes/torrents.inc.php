<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
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
            $gbytes = round(bytesToGB($item['size']), 2);
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
