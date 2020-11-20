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

    $item = mediadb_getByDbId($wanted_id, 'tmdb_search');

    if ($item === false) {
        return false;
    }

    if (isset($_POST['submit_wanted'])) {
        $id = $db->getLastId('wanted');
        $wanted_item[$id]['id'] = $id;
        $wanted_item[$id]['themoviedb_id'] = $item['themoviedb_id'];
        $wanted_item[$id]['title'] = $item['title'];
        $wanted_item[$id]['qualitys'] = $cfg['TORRENT_QUALITYS_PREFS'];
        $wanted_item[$id]['ignores'] = $cfg['TORRENT_IGNORES_PREFS'];
        $wanted_item[$id]['added'] = time();
        $wanted_item[$id]['day_check'] = $_POST['check_day'];
        $wanted_item[$id]['last_check'] = '';
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

function wanted_episode($id, $season, $episodes) {
    global $db, $cfg;

    $wanted_item = [];
    //echo $id .':'. $season .':'. $episode .'<br>';
    if (strlen($season) == 1) {
        $season = "0" . $season;
    }
    $episodes = explode(',', $episodes);

    foreach ($episodes as $episode) {
        $episode = trim($episode);
        if (strlen($episode) == 1) {
            $episode = "0" . $episode;
        }

        $item = $db->getItemByField('tmdb_search', 'themoviedb_id', $id);
        $title_search = $item['title'] . ' S' . $season . 'E' . $episode;

        $wanted_item[$id]['id'] = $db->getLastId('wanted');
        $wanted_item[$id]['themoviedb_id'] = $item['themoviedb_id'];
        $wanted_item[$id]['title'] = $title_search;
        $wanted_item[$id]['qualitys'] = $cfg['TORRENT_QUALITYS_PREFS'];
        $wanted_item[$id]['ignores'] = $cfg['TORRENT_IGNORES_PREFS'];
        $wanted_item[$id]['added'] = time();
        $wanted_item[$id]['day_check'] = 'L_DAY_ALL';
        $wanted_item[$id]['last_check'] = '';
        $wanted_item[$id]['type'] = 'shows';
        $wanted_item[$id]['season'] = $season;
        $wanted_item[$id]['episode'] = $episode;

        $db->addUniqElements('wanted', $wanted_item, 'title');
    }
}
