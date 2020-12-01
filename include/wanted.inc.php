<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function wanted_list() {
    global $db, $cfg, $LNG, $trans;
    $iurl = '?page=wanted';

    //update wanted agains transmission-daemon
    $trans->updateWanted();

    $wanted_list = $db->getTableData('wanted');

    if (!empty($wanted_list)) {
        $wanted_list_data = '';

        foreach ($wanted_list as $wanted_item) {
            $tdata = [];
            $tdata['iurl'] = $iurl;

            if (empty($wanted_item['id']) || $wanted_item['direct'] == 1) {
                continue;
            }

            $tdata['status_name'] = $LNG['L_SEARCHING'];
            if (isset($wanted_item['wanted_status']) && ($wanted_item['wanted_status'] >= 0)) {
                $tdata['status_name'] = $trans->getStatusName($wanted_item['wanted_status']);
            }
            !empty($wanted_item['ignore']) ? $tdata['ignore_link'] = $LNG['L_UNIGNORE'] : $tdata['ignore_link'] = $LNG['L_IGNORE'];
            $wanted_item['day_check'] = day_check($wanted_item['id'], $wanted_item['day_check']);
            $wanted_item['added'] = strftime("%x", $wanted_item['added']);
            !empty($wanted_item['last_check']) ? $wanted_item['last_check'] = strftime("%A %H:%M", $wanted_item['last_check']) : $wanted_item['last_check'] = $LNG['L_NEVER'];
            $mediadb_item = mediadb_getByDbId($wanted_item['media_type'], $wanted_item['themoviedb_id']);
            !empty($mediadb_item) ? $tdata['elink'] = $mediadb_item['elink'] : null;

            $wanted_list_data .= getTpl('wanted-item', array_merge($wanted_item, $tdata, $LNG, $cfg));
        }
        return $wanted_list_data;
    }
    return false;
}

function wanted_movies($wanted_id) {
    global $db, $cfg, $log;

    $item = [];
    $wanted_item = [];
    $wanted_type = 'movies';

    $item = mediadb_getByDbId('movies', $wanted_id);

    if ($item === false) {
        $log->debug('Wanted, seems that movie id not exists in the db ');
        return false;
    }

    $id = $db->getLastId('wanted');
    $wanted_item[$id]['id'] = $id;
    $wanted_item[$id]['themoviedb_id'] = $item['themoviedb_id'];
    $wanted_item[$id]['title'] = $item['title'];
    $wanted_item[$id]['qualitys'] = $cfg['TORRENT_QUALITYS_PREFS'];
    $wanted_item[$id]['ignores'] = $cfg['TORRENT_IGNORES_PREFS'];
    $wanted_item[$id]['added'] = time();
    $wanted_item[$id]['day_check'] = 'L_DAY_ALL';
    $wanted_item[$id]['last_check'] = '';
    $wanted_item[$id]['direct'] = 0;
    $wanted_item[$id]['wanted_status'] = -1;
    $wanted_item[$id]['media_type'] = $wanted_type;
    $wanted_item[$id]['profile'] = (int) $cfg['profile'];
    $db->addUniqElements('wanted', $wanted_item, 'themoviedb_id');
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

        $item = mediadb_getByDbId('shows', $id);
        $title_search = $item['title'] . ' S' . $season . 'E' . $episode;

        $wanted_item[$id]['id'] = $db->getLastId('wanted');
        $wanted_item[$id]['themoviedb_id'] = $item['themoviedb_id'];
        $wanted_item[$id]['title'] = $title_search;
        $wanted_item[$id]['qualitys'] = $cfg['TORRENT_QUALITYS_PREFS'];
        $wanted_item[$id]['ignores'] = $cfg['TORRENT_IGNORES_PREFS'];
        $wanted_item[$id]['added'] = time();
        $wanted_item[$id]['day_check'] = 'L_DAY_ALL';
        $wanted_item[$id]['last_check'] = '';
        $wanted_item[$id]['direct'] = 0;
        $wanted_item[$id]['wanted_status'] = -1;
        $wanted_item[$id]['media_type'] = 'shows';
        $wanted_item[$id]['season'] = $season;
        $wanted_item[$id]['episode'] = $episode;
        $wanted_item[$id]['profile'] = (int) $cfg['profile'];

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
    $data .= '<option ' . $sel_mon . ' value="L_DAY_MON">' . $LNG['L_DAY_MON']['name'] . '</option>';
    $data .= '<option ' . $sel_tue . ' value="L_DAY_TUE">' . $LNG['L_DAY_TUE']['name'] . '</option>';
    $data .= '<option ' . $sel_wed . ' value="L_DAY_WED">' . $LNG['L_DAY_WED']['name'] . '</option>';
    $data .= '<option ' . $sel_thu . ' value="L_DAY_THU">' . $LNG['L_DAY_THU']['name'] . '</option>';
    $data .= '<option ' . $sel_fri . ' value="L_DAY_FRI">' . $LNG['L_DAY_FRI']['name'] . '</option>';
    $data .= '<option ' . $sel_sat . ' value="L_DAY_SAT">' . $LNG['L_DAY_SAT']['name'] . '</option>';
    $data .= '<option ' . $sel_sun . ' value="L_DAY_SUN">' . $LNG['L_DAY_SUN']['name'] . '</option>';
    $data .= '</select>';
    $data .= '</form>';

    return $data;
}
