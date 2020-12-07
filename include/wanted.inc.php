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
            $wanted_item['added'] = strftime("%x", strtotime($wanted_item['added']));
            !empty($wanted_item['last_check']) ? $wanted_item['last_check'] = strftime("%A %H:%M", $wanted_item['last_check']) : $wanted_item['last_check'] = $LNG['L_NEVER'];
            $mediadb_item = mediadb_getByDbId($wanted_item['media_type'], $wanted_item['themoviedb_id']);
            !empty($mediadb_item) ? $tdata['elink'] = $mediadb_item['elink'] : null;
            $wanted_item['media_type'] == 'movies' ? $tdata['lang_media_type'] = $LNG['L_MOVIES'] : $tdata['lang_media_type'] = $LNG['L_SHOWS'];
            $wanted_list_data .= getTpl('wanted-item', array_merge($wanted_item, $tdata, $LNG, $cfg));
        }
        return $wanted_list_data;
    }
    return false;
}

function wanted_movies($wanted_id) {
    global $db, $cfg, $log;

    $item = [];

    $wanted_type = 'movies';

    $item = mediadb_getByDbId('movies', $wanted_id);

    if ($item === false) {
        $log->debug('Wanted, seems that movie id not exists in the db ');
        return false;
    }

    $wanted_item = [
        'themoviedb_id' => $item['themoviedb_id'],
        'title' => $item['title'],
        'added' => time(),
        'day_check' => 0,
        'last_check' => '',
        'direct' => 0,
        'wanted_status' => -1,
        'media_type' => $wanted_type,
        'profile' => (int) $cfg['profile'],
    ];
    //FIXME Can be two equal tmdb ids, movie and show change this
    $db->addItemUniqField('wanted', $wanted_item, 'themoviedb_id');
}

function wanted_episode($id, $season, $episodes) {
    global $db, $cfg;

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

        $wanted_item = [
            'themoviedb_id' => $item['themoviedb_id'],
            'title' => $title_search,
            'day_check' => 0,
            'last_check' => '',
            'direct' => 0,
            'wanted_status' => -1,
            'media_type' => 'shows',
            'season' => $season,
            'episode' => $episode,
            'profile' => (int) $cfg['profile'],
        ];
        $db->addItemUniqField('wanted', $wanted_item, 'title');
    }
}

function day_check($id, $day_time) {
    global $LNG;

    $data = '';
    $sel_all = $sel_mon = $sel_tue = $sel_wed = $sel_thu = $sel_fri = $sel_sat = $sel_sun = '';
    switch ($day_time) {
        case 0:
            $sel_all = 'selected';
            break;
        case 1:
            $sel_mon = 'selected';
            break;
        case 2:
            $sel_tue = 'selected';
            break;
        case 3:
            $sel_wed = 'selected';
            break;
        case 4:
            $sel_thu = 'selected';
            break;
        case 5:
            $sel_fri = 'selected';
            break;
        case 6:
            $sel_sat = 'selected';
            break;
        case 7:
            $sel_sun = 'selected';
            break;
    }
    $data .= '<form class="form_inline" method="POST" action="">';
    $data .= '<select onchange="this.form.submit()" name="check_day[' . $id . ']">';
    $data .= '<option ' . $sel_all . ' value="0">' . $LNG['L_DAY_ALL'] . '</option>';
    $data .= '<option ' . $sel_mon . ' value="1">' . $LNG['L_DAY_MON'] . '</option>';
    $data .= '<option ' . $sel_tue . ' value="2">' . $LNG['L_DAY_TUE'] . '</option>';
    $data .= '<option ' . $sel_wed . ' value="3">' . $LNG['L_DAY_WED'] . '</option>';
    $data .= '<option ' . $sel_thu . ' value="4">' . $LNG['L_DAY_THU'] . '</option>';
    $data .= '<option ' . $sel_fri . ' value="5">' . $LNG['L_DAY_FRI'] . '</option>';
    $data .= '<option ' . $sel_sat . ' value="6">' . $LNG['L_DAY_SAT'] . '</option>';
    $data .= '<option ' . $sel_sun . ' value="7">' . $LNG['L_DAY_SUN'] . '</option>';
    $data .= '</select>';
    $data .= '</form>';

    return $data;
}
