<?php

/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function wanted_movies($wanted_id) {
    global $db, $cfg;

    $item = [];
    $wanted_item = [];

    $wanted_type = 'movies';

    $item = db_get_by_db_id($wanted_id, 'tmdb_search');

    if ($item === false) {
        return false;
    }

    if (isset($_POST['submit_wanted'])) {
        $id = $db->getLastID('wanted');
        $wanted_item[$id]['id'] = $id;
        $wanted_item[$id]['themoviedb_id'] = $item['themoviedb_id'];
        $wanted_item[$id]['title'] = $item['title'];
        $wanted_item[$id]['qualitys'] = $cfg['TORRENT_QUALITYS_PREFS'];
        $wanted_item[$id]['ignores'] = $cfg['TORRENT_IGNORES_PREFS'];
        $wanted_item[$id]['added'] = time();
        $wanted_item[$id]['day_check'] = $_POST['check_day'];
        $wanted_item[$id]['type'] = $wanted_type;
        $db->addUniqElements('wanted', $wanted_item, 'themoviedb_id');
    }

    $item['tags_quality'] = '';
    $item['tags_ignore'] = '';
    $item['tag_type'] = '<span class="tag_type">' . $wanted_type . '</span>';
    foreach ($cfg['TORRENT_QUALITYS_PREFS'] as $quality) {
        $item['tags_quality'] .= '<span class="tag_quality">' . $quality . '</span>';
    }
    foreach ($cfg['TORRENT_IGNORES_PREFS'] as $ignores) {
        $item['tags_ignore'] .= '<span class="tag_ignore">' . $ignores . '</span>';
    }

    return $item;
}
