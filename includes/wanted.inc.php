<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function wanted_list() {
    global $db, $LNG, $trans;

    $wanted['templates'] = [];
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
        $wanted_list_tmp['TRACKING'] = [];
        $wanted_list_tmp['SEARCHING'] = [];
        $wanted_list_tmp['COMPLETED'] = [];
        $wanted_list_tmp['DOWNLOADING'] = [];
        $wanted_list_tmp['MOVED'] = [];
        $wanted_list_tmp['SEEDING'] = [];
        $wanted_list_tmp['OTHER'] = [];
        $wanted_list_tmp['DELETED'] = [];

        foreach ($wanted_list as $wanted_item) {
            $tdata = [];
            $tdata['iurl'] = $iurl;

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

            $wanted_item['created'] = timestamp_to_date(strtotime($wanted_item['created']), "Y");
            !empty($wanted_item['last_check']) ? $wanted_item['last_check'] = custom_strftime("D H:M", $wanted_item['last_check']) : $wanted_item['last_check'] = $LNG['L_NEVER'];
            if ($wanted_item['media_type'] == 'shows') {
                (strlen($wanted_item['season']) == 1) ? $season = '0' . $wanted_item['season'] : $season = $wanted_item['season'];
                (strlen($wanted_item['episode']) == 1) ? $episode = '0' . $wanted_item['episode'] : $episode = $wanted_item['episode'];
                $s_episode = 'S' . $season . 'E' . $episode;
                ($wanted_item['track_show'] == 1) ? $s_episode = '>=' . $s_episode : null;
                $wanted_item['show_title'] = $wanted_item['title'] . ' ' . $s_episode;
            }
            $odb_item = mediadb_getMediaData($wanted_item['media_type'], $wanted_item['themoviedb_id']);

            if ($odb_item == false) {
                //moviedb id no longer exists remove from wanted?
                continue;
            }
            $tdata['poster'] = get_poster($odb_item);

            $tdata['elink'] = $odb_item['elink'];

            ($wanted_item['media_type'] == 'movies') ? $view_type = 'movies_db' : $view_type = 'shows_db';
            $tdata['link'] = '?page=view&id=' . $odb_item['id'] . '&view_type=' . $view_type;
            if (!empty($wanted_item['show_title'])) {
                $tdata['link_name'] = $wanted_item['show_title'];
            } else {
                $tdata['link_name'] = $wanted_item['title'];
            }

            $wanted_item['media_type'] == 'movies' ? $tdata['lang_media_type'] = $LNG['L_MOVIES'] : $tdata['lang_media_type'] = $LNG['L_SHOWS'];

            if ($wanted_item['track_show'] == 1) {
                array_push($wanted_list_tmp['TRACKING'], array_merge($wanted_item, $tdata));
            } else if ($wanted_item['wanted_status'] == 4) {
                array_push($wanted_list_tmp['DOWNLOADING'], array_merge($wanted_item, $tdata));
            } else if ($wanted_item['wanted_status'] == -1) {
                array_push($wanted_list_tmp['SEARCHING'], array_merge($wanted_item, $tdata));
            } else if ($wanted_item['wanted_status'] == 8) {
                array_push($wanted_list_tmp['COMPLETED'], array_merge($wanted_item, $tdata));
            } else if ($wanted_item['wanted_status'] == 9) {
                array_push($wanted_list_tmp['MOVED'], array_merge($wanted_item, $tdata));
            } else if ($wanted_item['wanted_status'] == 6) {
                array_push($wanted_list_tmp['SEEDING'], array_merge($wanted_item, $tdata));
            } else if ($wanted_item['wanted_status'] == 10) {
                array_push($wanted_list_tmp['DELETED'], array_merge($wanted_item, $tdata));
            } else {
                array_push($wanted_list_tmp['OTHER'], array_merge($wanted_item, $tdata));
            }
        }

        if (valid_array($wanted_list_tmp)) {

            if (count($wanted_list_tmp['OTHER']) > 0) {
                $wanted['templates'][] = ['name' => 'wanted_other', 'tpl_vars' => $wanted_list_tmp['OTHER'], 'tpl_common_vars' => ['tpl_head' => $LNG['L_OTHER']]];
            }
            if (count($wanted_list_tmp['DOWNLOADING']) > 0) {
                $wanted['templates'][] = ['name' => 'wanted_download', 'tpl_vars' => $wanted_list_tmp['DOWNLOADING'], 'tpl_common_vars' => ['tpl_head' => $LNG['L_DOWNLOADING']]];
            }
            if (!empty($wanted_list_tmp['COMPLETED'])) {
                $wanted['templates'][] = ['name' => 'wanted_completed', 'tpl_vars' => $wanted_list_tmp['COMPLETED'], 'tpl_common_vars' => ['tpl_head' => $LNG['L_COMPLETED']]];
            }
            if (!empty($wanted_list_tmp['DELETED'])) {
                $wanted['templates'][] = ['name' => 'wanted_deleted', 'tpl_vars' => $wanted_list_tmp['DELETED'], 'tpl_common_vars' => ['tpl_head' => $LNG['L_DELETED']]];
            }
            if (!empty($wanted_list_tmp['MOVED'])) {
                $wanted['templates'][] = ['name' => 'wanted_moved', 'tpl_vars' => $wanted_list_tmp['MOVED'], 'tpl_common_vars' => ['tpl_head' => $LNG['L_MOVED']]];
            }
            if (!empty($wanted_list_tmp['SEEDING'])) {
                $wanted['templates'][] = ['name' => 'wanted_seeding', 'tpl_vars' => $wanted_list_tmp['SEEDING'], 'tpl_common_vars' => ['tpl_head' => $LNG['L_SEEDING']]];
            }
            if (!empty($wanted_list_tmp['SEARCHING'])) {
                $wanted['templates'][] = ['name' => 'wanted_searching', 'tpl_vars' => $wanted_list_tmp['SEARCHING'], 'tpl_common_vars' => ['tpl_head' => $LNG['L_SEARCHING']]];
            }

            //Fill common and necesary vars
            foreach ($wanted['templates'] as &$template) {
                $template['tpl_file'] = 'wanted-item';
                $template['tpl_place'] = 'wanted';
                $template['tpl_place_var'] = 'wanted_list';
                $template['tpl_pri'] = 5;
            }

            if (!empty($wanted_list_tmp['TRACKING'])) {
                $wanted['templates'][] = [
                    'name' => 'wanted_tracking',
                    'tpl_file' => 'wanted-item',
                    'tpl_place' => 'wanted',
                    'tpl_place_var' => 'track_show_list',
                    'tpl_pri' => 5,
                    'tpl_vars' => $wanted_list_tmp['TRACKING'],
                    'tpl_common_vars' => ['tpl_head' => $LNG['L_TRACKING']],
                ];
            }
        }
    }
    return $wanted;
}

function wanted_movies($wanted_id) {
    global $db, $log, $user;

    $item = [];
    $wanted_type = 'movies';
    $item = mediadb_getMediaData('movies', $wanted_id);

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
    !empty($user) && !empty($user->getId()) ? $uid = $user->getId() : $uid = 0; //cli use wanted_episode but users is null
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

        $item = mediadb_getMediaData('shows', $id);

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
