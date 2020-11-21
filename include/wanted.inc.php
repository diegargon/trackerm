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
        $wanted_item[$id]['profile'] = $cfg['profile'];
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
        $wanted_item[$id]['profile'] = $cfg['profile'];

        $db->addUniqElements('wanted', $wanted_item, 'title');
    }
}

function day_check($id, $day_time) {
    global $LNG;

    $data = '';
    $sel_all = $sel_mon = $sel_tue = $sel_wed = $sel_thu = $sel_fri = $sel_sat = $sel_sun = '';
    switch ($day_time) {
        case 'L_DAY_ALL':
            $sel_all = 'selected';
            break;
        case 'L_DAY_MON':
            $sel_mon = 'selected';
            break;
        case 'L_DAY_TUE':
            $sel_tue = 'selected';
            break;
        case 'L_DAY_WED':
            $sel_wed = 'selected';
            break;
        case 'L_DAY_THU':
            $sel_thu = 'selected';
            break;
        case 'L_DAY_FRI':
            $sel_fri = 'selected';
            break;
        case 'L_DAY_SAT':
            $sel_sat = 'selected';
            break;
        case 'L_DAY_SUN':
            $sel_sun = 'selected';
            break;
    }
    $data .= '<form class="form_inline" method="POST" action="">';
    $data .= '<select onchange="this.form.submit()" name="check_day[' . $id . ']">';
    $data .= '<option ' . $sel_all . ' value="L_DAY_ALL">' . $LNG['L_DAY_ALL'] . '</option>';
    $data .= '<option ' . $sel_mon . ' value="L_DAY_MON">' . $LNG['L_DAY_MON'] . '</option>';
    $data .= '<option ' . $sel_tue . ' value="L_DAY_TUE">' . $LNG['L_DAY_TUE'] . '</option>';
    $data .= '<option ' . $sel_wed . ' value="L_DAY_WED">' . $LNG['L_DAY_WED'] . '</option>';
    $data .= '<option ' . $sel_thu . ' value="L_DAY_THU">' . $LNG['L_DAY_THU'] . '</option>';
    $data .= '<option ' . $sel_fri . ' value="L_DAY_FRI">' . $LNG['L_DAY_FRI'] . '</option>';
    $data .= '<option ' . $sel_sat . ' value="L_DAY_SAT">' . $LNG['L_DAY_SAT'] . '</option>';
    $data .= '<option ' . $sel_sun . ' value="L_DAY_SUN">' . $LNG['L_DAY_SUN'] . '</option>';
    $data .= '</select>';
    $data .= '</form>';


    return $data;
}
