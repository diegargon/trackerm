<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function wanted_list() {
    global $db, $LNG, $trans, $frontend;

    $iurl = '?page=wanted';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['ignore_tags']) && valid_array($_POST['ignore_tags'])) {
            foreach ($_POST['ignore_tags'] as $ignore_key => $ignore_value) {
                $db->updateItemById('wanted', $ignore_key, ['custom_words_ignore' => $ignore_value]);
            }
        }
        if (isset($_POST['require_tags']) && valid_array($_POST['require_tags'])) {
            foreach ($_POST['require_tags'] as $require_key => $require_value) {
                $db->updateItemById('wanted', $require_key, ['custom_words_require' => $require_value]);
            }
        }
        if (isset($_POST['custom_title']) && valid_array($_POST['custom_title'])) {
            foreach ($_POST['custom_title'] as $c_title_key => $c_title_value) {
                $db->updateItemById('wanted', $c_title_key, ['custom_title' => $c_title_value]);
            }
        }

        if (isset($_POST['only_proper'])) {
            $id_only_proper = array_key_first($_POST['only_proper']);
            $_POST['only_proper'][$id_only_proper] == 0 ? $only_proper = 0 : $only_proper = 1;

            $db->updateItemById('wanted', $id_only_proper, ['only_proper' => $only_proper]);
        }
        if (isset($_POST['nocount'])) {
            $id_nocount = array_key_first($_POST['nocount']);
            $_POST['nocount'][$id_nocount] == 0 ? $nocount = 0 : $nocount = 1;

            $db->updateItemById('wanted', $id_nocount, ['ignore_count' => $nocount]);
        }

        if (!empty($_POST['track_show']) && !empty($_POST['id'])) {
            $id = Filter::postInt('id');
            $track_show = Filter::postString('track_show');
            $track_show = ltrim($track_show, 'S');
            $track_show = explode('E', $track_show);
            $season = $track_show[0];
            $episode = $track_show[1];
            wanted_episode($id, $season, $episode, 1);
        }
    }

    $wanted_list = $db->getTableData('wanted');

    if (valid_array($wanted_list)) {
        $wanted_list_data = null;
        $wanted_list_tmp_data['TRACKING'] = '';
        $wanted_list_tmp_data['SEARCHING'] = '';
        $wanted_list_tmp_data['MOVED'] = '';
        $wanted_list_tmp_data['SEEDING'] = '';
        $wanted_list_tmp_data['OTHER'] = '';
        $wanted_list_tmp_data['DELETED'] = '';

        foreach ($wanted_list as $wanted_item) {
            $tdata = [];
            $tdata['iurl'] = $iurl;
            $tdata['want_separator'] = 0;

            if (empty($wanted_item['id']) || $wanted_item['direct'] == 1) {
                continue;
            }

            $tdata['status_name'] = $LNG['L_SEARCHING'];
            if (!empty($wanted_item['track_show'])) {
                $tdata['status_name'] = $LNG['L_TRACKING'];
            }
            if (isset($wanted_item['wanted_status']) && ($wanted_item['wanted_status'] >= 0)) {
                !empty($trans) ? $tdata['status_name'] = $trans->getStatusName($wanted_item['wanted_status']) : $tdata['status_name'] = $wanted_item['wanted_status'];
            }

            $wanted_item['day_check'] = day_check($wanted_item['id'], $wanted_item['day_check'], $wanted_item['wanted_status']);
            $wanted_item['created'] = strftime("%x", strtotime($wanted_item['created']));
            !empty($wanted_item['last_check']) ? $wanted_item['last_check'] = strftime("%a %H:%M", $wanted_item['last_check']) : $wanted_item['last_check'] = $LNG['L_NEVER'];
            if ($wanted_item['media_type'] == 'shows') {
                (strlen($wanted_item['season']) == 1) ? $season = '0' . $wanted_item['season'] : $season = $wanted_item['season'];
                (strlen($wanted_item['episode']) == 1) ? $episode = '0' . $wanted_item['episode'] : $episode = $wanted_item['episode'];
                $s_episode = 'S' . $season . 'E' . $episode;
                ($wanted_item['track_show'] == 1) ? $s_episode = '>=' . $s_episode : null;
                $wanted_item['show_title'] = $wanted_item['title'] . ' ' . $s_episode;
            }
            $mediadb_item = mediadb_getFromCache($wanted_item['media_type'], $wanted_item['themoviedb_id']);
            $tdata['elink'] = $mediadb_item['elink'];

            ($wanted_item['media_type'] == 'movies') ? $view_type = 'movies_db' : $view_type = 'shows_db';
            $tdata['link'] = '?page=view&id=' . $mediadb_item['id'] . '&view_type=' . $view_type;
            if (!empty($wanted_item['show_title'])) {
                $tdata['link_name'] = $wanted_item['show_title'];
            } else {
                $tdata['link_name'] = $wanted_item['title'];
            }

            $wanted_item['media_type'] == 'movies' ? $tdata['lang_media_type'] = $LNG['L_MOVIES'] : $tdata['lang_media_type'] = $LNG['L_SHOWS'];

            if ($wanted_item['track_show'] == 1) {
                $wanted_list_tmp_data['TRACKING'] .= $frontend->getTpl('wanted-item', array_merge($wanted_item, $tdata));
            } else if ($wanted_item['wanted_status'] == -1) {
                $wanted_list_tmp_data['SEARCHING'] .= $frontend->getTpl('wanted-item', array_merge($wanted_item, $tdata));
            } else if ($wanted_item['wanted_status'] == 9) {
                $wanted_list_tmp_data['MOVED'] .= $frontend->getTpl('wanted-item', array_merge($wanted_item, $tdata));
            } else if ($wanted_item['wanted_status'] == 6) {
                $wanted_list_tmp_data['SEEDING'] .= $frontend->getTpl('wanted-item', array_merge($wanted_item, $tdata));
            } else if ($wanted_item['wanted_status'] == 10) {
                $wanted_list_tmp_data['DELETED'] .= $frontend->getTpl('wanted-item', array_merge($wanted_item, $tdata));
            } else {
                $wanted_list_tmp_data['OTHER'] .= $frontend->getTpl('wanted-item', array_merge($wanted_item, $tdata));
            }
        }
        //IMPROVE: Sorting: do better way
        if (valid_array($wanted_list_tmp_data)) {
            isset($wanted_list_tmp_data['DELETED']) ? $wanted_list_data .= $wanted_list_tmp_data['DELETED'] : null;
            isset($wanted_list_tmp_data['MOVED']) ? $wanted_list_data .= $wanted_list_tmp_data['MOVED'] : null;
            isset($wanted_list_tmp_data['SEEDING']) ? $wanted_list_data .= $wanted_list_tmp_data['SEEDING'] : null;
            isset($wanted_list_tmp_data['SEARCHING']) ? $wanted_list_data .= $wanted_list_tmp_data['SEARCHING'] : null;
            isset($wanted_list_tmp_data['OTHER']) ? $wanted_list_data .= $wanted_list_tmp_data['OTHER'] : null;
            if ($wanted_list_tmp_data['TRACKING']) {
                $wanted_list_data .= $frontend->getTpl('wanted-item', ['want_separator' => 1]);
                $wanted_list_data .= $wanted_list_tmp_data['TRACKING'];
            }
        }


        return $wanted_list_data;
    }
    return false;
}

function wanted_movies($wanted_id) {
    global $db, $log, $user;

    $item = [];
    $wanted_type = 'movies';
    $item = mediadb_getFromCache('movies', $wanted_id);

    if ($item === false) {
        $log->debug('Wanted, seems the movie id not exists in the db ');
        return false;
    }

    $wanted_item = [
        'themoviedb_id' => $item['themoviedb_id'],
        'title' => $item['title'],
        'day_check' => 0,
        'last_check' => '',
        'direct' => 0,
        'wanted_status' => -1,
        'media_type' => $wanted_type,
        'profile' => $user->getId(),
    ];

    $where_check = [
        'themoviedb_id' => ['value' => $item['themoviedb_id']],
        'media_type' => ['value' => $wanted_type],
    ];
    $result = $db->select('wanted', 'id', $where_check, 'LIMIT 1');
    $dup_item = $db->fetch($result);
    $db->finalize($result);

    (!$dup_item) ? $db->insert('wanted', $wanted_item) : null;
}

function wanted_episode($id, $season, $episodes, $track_show = 0, $inherint_track = null) {
    global $db, $user;

    (strlen($season) == 1) ? $season = '0' . $season : null;
    $episodes = explode(',', $episodes);
    !empty($user->getId()) ? $uid = $user->getId() : $uid = 0;
    $track_show == null ? $track_show = 0 : null;

    if (valid_array($inherint_track)) {
        isset($inherint_track['day_check']) ? $day_check = $inherint_track['day_check'] : $day_check = null;
        isset($inherint_track['custom_words_require']) ? $custom_words_require = $inherint_track['custom_words_require'] : $custom_words_require = null;
        isset($inherint_track['custom_words_ignore']) ? $custom_words_ignore = $inherint_track['custom_words_ignore'] : $custom_words_ignore = null;
        isset($inherint_track['custom_title']) ? $custom_title = $inherint_track['custom_title'] : $custom_title = null;
    } else {
        $day_check = null;
        $custom_words_ignore = null;
        $custom_words_require = null;
        $custom_title = null;
    }

    foreach ($episodes as $episode) {
        $episode = trim($episode);
        (strlen($episode) == 1) ? $episode = '0' . $episode : null;

        $item = mediadb_getFromCache('shows', $id);

        $wanted_item = [
            'themoviedb_id' => $item['themoviedb_id'],
            'title' => $item['title'],
            'day_check' => $day_check,
            'last_check' => '',
            'direct' => 0,
            'wanted_status' => -1,
            'media_type' => 'shows',
            'season' => $season,
            'episode' => $episode,
            'profile' => $uid,
            'track_show' => $track_show,
            'custom_words_require' => $custom_words_require,
            'custom_words_ignore' => $custom_words_ignore,
            'custom_title' => $custom_title,
        ];

        if ($track_show) {
            $where_check = [
                'themoviedb_id' => ['value' => $wanted_item['themoviedb_id']],
                'track_show' => ['value' => 1],
            ];
        } else {
            $where_check = [
                'themoviedb_id' => ['value' => $wanted_item['themoviedb_id']],
                'season' => ['value' => $season],
                'episode' => ['value' => $episode],
                'track_show' => ['value' => 0],
            ];
        }
        $result = $db->select('wanted', 'id', $where_check, 'LIMIT 1');
        $item = $db->fetch($result);
        $db->finalize($result);
        (!$item) ? $db->insert('wanted', $wanted_item) : null;
    }
}

function day_check($id, $day_time, $wanted_status) {
    global $LNG;

    $data = '';
    $sel_all = $sel_never = $sel_mon = $sel_tue = $sel_wed = $sel_thu = $sel_fri = $sel_sat = $sel_sun = '';

    switch ($day_time) {
        case -1:
            $sel_never = 'selected';
            break;
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

    ($wanted_status != -1 ) ? $disabled = 'disabled' : $disabled = '';
    $data .= '<form class="form_inline" method="POST" action="">';
    $data .= '<select ' . $disabled . ' onchange="this.form.submit()" name="check_day[' . $id . ']">';
    $data .= '<option ' . $sel_never . ' value="-1">' . $LNG['L_NEVER'] . '</option>';
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
