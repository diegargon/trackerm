<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function view() {
    global $cfg, $db, $frontend;

    $view_type = Filter::getString('view_type');
    $id = Filter::getInt('id');
    empty($id) ? $frontend->msgBox($msg = ['title' => 'L_ERROR', 'body' => 'L_ERR_BAD_ID']) : null;
    $other = [];
    $page = '';

    if ($view_type == 'movies_library') {
        $table = 'library_master_movies';
        $media_type = 'movies';
        $other['reidentify'] = 1;
        $other['deletereg'] = 1;
    } else if ($view_type == 'shows_library') {
        $table = 'library_master_shows';
        $media_type = 'shows';
        $other['reidentify'] = 1;
        $other['deletereg'] = 1;
    } else if ($view_type == 'movies_torrent') {
        $table = 'jackett_movies';
        $media_type = 'movies';
    } else if ($view_type == 'shows_torrent') {
        $table = 'jackett_shows';
        $media_type = 'shows';
    } else if ($view_type == 'movies_db') {
        $table = 'tmdb_search_movies';
        $media_type = 'movies';
        $other['wanted'] = 1;
    } else if ($view_type == 'shows_db') {
        $table = 'tmdb_search_shows';
        $media_type = 'shows';
    } else {
        return false;
    }
    $other['view_type'] = $view_type;
    $item = $db->getItemById($table, $id);

    if (empty($item)) {
        return $frontend->msgBox($msg = ['title' => 'L_ERROR', 'body' => 'L_ITEM_NOT_FOUND']);
    }

    if ($view_type == 'movies_db' || $view_type == 'shows_db') {
        //TODO master no tmdbid
        $in_library = $db->getItemByField('library_master_' . $media_type, 'themoviedb_id', $item['themoviedb_id']);
        if ($in_library !== false) {
            $item['in_library'] = $in_library['id'];
        }
    }

    if ($view_type == 'shows_library' || $view_type == 'shows_db') {
        isset($_GET['update']) ? $update = true : $update = false;
        $other['seasons_data'] = view_seasons($item, $update);
    }
    !empty($item['total_size']) ? $item['total_size'] = human_filesize($item['total_size']) : null;

    if ($cfg['cache_images']) {
        if (!empty($item['poster'])) {
            $cache_img_response = cache_img($item['poster']);
            if ($cache_img_response !== false) {
                $item['poster'] = $cache_img_response;
            }
        } else if (!empty($item['guessed_poster']) && $item['guessed_poster'] != -1) {
            $cache_img_response = cache_img($item['guessed_poster']);
            if ($cache_img_response !== false) {
                $item['poster'] = $cache_img_response;
            }
        }
    } else if (empty($item['poster']) && !empty($item['guessed_poster'])) {
        $item['pòster'] = $item['guessed_poster'];
    }
    empty($item['poster']) ? $item['poster'] = $cfg['img_url'] . '/not_available.jpg' : null;

    $other['extra'] = '';
    $opt['auto_show_torrents'] = 0;
    $opt['auto_show_db'] = 0;

    ($view_type == 'movies_db' || $view_type == 'shows_db') ? $opt['auto_show_torrents'] = 1 : null;
    ($view_type == 'movies_torrent' || $view_type == 'shows_torrent') ? $opt['auto_show_db'] = 1 : null;

    if ($view_type == 'movies_torrent' || $view_type == 'movies_db' || $view_type == 'movies_library') {
        empty($item['media_type']) ? $item['media_type'] = 'movies' : null;
        $other['extra'] .= view_extra_movies($item, $opt);
    }

    if ($view_type == 'shows_torrent' || $view_type == 'shows_db' || $view_type == 'shows_library') {
        empty($item['media_type']) ? $item['media_type'] = 'shows' : null;
        $other['extra'] .= view_extra_shows($item, $opt);
    }

    if ($view_type == 'shows_db' || $view_type == 'shows_library') {
        $other['follow_show'] = get_follow_show($item['themoviedb_id']);
    }

    if ($view_type == 'movies_library' || $view_type == 'shows_library') {
        $other['media_files'] = get_media_files($id, $media_type);
    }

    $page = $frontend->getTpl('view', array_merge($item, $other));

    return $page;
}

function view_extra_movies($item, $opt = null) {
    global $frontend;

    $id = Filter::getInt('id');
    $page = Filter::getString('page');
    $view_type = Filter::getString('view_type');
    $title = getFileTitle($item['title']);
    (!empty($_GET['search_movies_db'])) ? $stitle = trim(Filter::getUtf8('search_movies_db')) : $stitle = $title;
    $stitle = preg_replace('/\s\d{4}/', '', $stitle);

    $extra = $frontend->getTpl('view_extra_movies', ['page' => $page, 'id' => $id, 'view_type' => $view_type, 'stitle' => $stitle]);

    if (isset($_GET['more_movies']) || (!empty($opt['auto_show_db']) && !isset($_GET['more_torrents']))) {
        $movies = mediadb_searchMovies($stitle);
        $opt['view_type'] = 'movies_db';
        !empty($movies) ? $extra .= buildTable('L_DB', $movies, $opt) : null;
    }

    if (isset($_GET['more_torrents']) || (!empty($opt['auto_show_torrents']) && !isset($_GET['more_movies']))) {
        $search['words'] = $stitle;
        $m_results = search_media_torrents('movies', $search, 'L_TORRENT');
        if (valid_array($m_results)) {
            $m_results = mix_media_res($m_results);
            $topt['view_type'] = 'movies_torrent';
            $extra .= buildTable('L_TORRENT', $m_results, $topt);
        } else {
            $extra .= $frontend->msgBox(['title' => 'L_TORRENT', 'body' => 'L_NOTHING_FOUND']);
        }
    }

    return $extra;
}

function view_extra_shows($item, $opt) {
    global $frontend;

    $id = Filter::getInt('id');
    $page = Filter::getString('page');
    $view_type = Filter::getString('view_type');
    $title = getFileTitle($item['title']);
    (!empty($_GET['search_shows_db'])) ? $stitle = trim(Filter::getString('search_shows_db')) : $stitle = $title;

    $extra = $frontend->getTpl('view_extra_shows', ['page' => $page, 'id' => $id, 'view_type' => $view_type, 'stitle' => $stitle]);

    if (isset($_GET['more_shows']) || (!empty($opt['auto_show_db']) && !isset($_GET['more_torrents']))) {
        $shows = mediadb_searchShows($stitle);
        $opt['view_type'] = 'shows_db';
        !empty($shows) ? $extra .= buildTable('L_DB', $shows, $opt) : null;
    }

    if (isset($_GET['more_torrents']) || (!empty($opt['auto_show_torrents']) && !isset($_GET['more_shows']))) {
        $search['words'] = $stitle;

        $m_results = search_media_torrents('shows', $search);
        if (valid_array($m_results)) {
            $m_results = mix_media_res($m_results);
            $topt['view_type'] = 'shows_torrent';
            $topt['more_torrents'] = 1;
            $extra .= buildTable('L_TORRENT', $m_results, $topt);
        } else {
            $extra .= $frontend->msgBox(['title' => 'L_TORRENT', 'body' => 'L_NOTHING_FOUND']);
        }
    }

    return $extra;
}

function view_seasons($item, $update = false) {
    global $db, $LNG;

    $id = Filter::getInt('id');
    $season = Filter::getInt('season');
    $view_type = Filter::getString('view_type');
    $seasons_data = '';
    $episode_data = '';

    //SUBMITED WANTED (episode=1 || episode=1,2,3
    if (Filter::getInt('wanted') && !empty($season)) {
        $episode = Filter::getInt('episode');

        if (empty($episode) && !empty($_GET['episode'])) {
            $episodes_check = explode(',', $_GET['episode']);

            if (valid_array($episodes_check) && Filter::varInt($episodes_check)) {
                $episode = Filter::getString('episode'); //episode string: "1,2,3..."
            }
        }
        !empty($episode) ? wanted_episode($item['themoviedb_id'], $season, $episode) : null;
    }

    if (empty($season)) {
        $item_details = $db->getItemByField('shows_details', 'themoviedb_id', $item['themoviedb_id']);
        if ($item_details === false || $update) {
            mediadb_getSeasons($item['themoviedb_id']);
            $item_details = $db->getItemByField('shows_details', 'themoviedb_id', $item['themoviedb_id']);
        }
    } else {
        $where['themoviedb_id'] = ['value' => $item['themoviedb_id']];
        $where['season'] = ['value' => $season];
        $results = $db->select('shows_details', null, $where);
        $items_details = $db->fetchAll($results);
        ($items_details === false || $update) ? $items_details = mediadb_getSeasons($item['themoviedb_id']) : null;
    }

    if (empty($item_details) && empty($items_details)) {
        return false;
    }
    if (!empty($item_details)) {
        $seasons = $item_details['seasons'];
        $episodes = $item_details['episodes'];
    } else {
        $seasons = $items_details[0]['seasons'];
        $episodes = $items_details[0]['episodes'];
    }

    $iurl = '?page=view&id=' . $id . '&view_type=' . $view_type;
    for ($i = 1; $i <= $seasons; $i++) {
        $seasons_data .= '<a class="season_link" href="' . $iurl . '&season=' . $i . '">' . $LNG['L_SEASON'] . ': ' . $i . '</a>';
    }
    $seasons_data .= '<br/><span>' . $LNG['L_SEASONS'] . ': ' . $seasons . ' ' . $LNG['L_EPISODES'] . ': ' . $episodes . '</span><br/>';

    if ($season) {
        $episode_data = view_season_detailed($season, $items_details);
    }
    $seasons_data .= '<br/>' . $episode_data;

    return $seasons_data;
}

function view_season_detailed($season, $items_details) {
    global $LNG, $frontend;

    $id = Filter::getInt('id');
    $view_type = Filter::getString('view_type');
    $iurl = '?page=view&id=' . $id . '&view_type=' . $view_type;
    $episode_data = '<div class="episode_container">';
    $episode_data .= '<hr/><div class="divTable">';
    $have_episodes = [];
    $item_counter = 0;

    $have_shows = get_have_shows_season($items_details[0]['themoviedb_id'], $items_details[0]['season']);
    foreach ($items_details as $item) {
        $tdata = [];
        $tdata['iurl'] = $iurl;
        if ($item['season'] == $season) {
            if ($item_counter == 12) { //Max Items per table
                $episode_data .= '</div>'; //Table
                $episode_data .= '</div>'; //Episode container
                $episode_data .= '<div class="episode_container">';
                $episode_data .= '<hr/><div class="divTable">';
                $item_counter = 0;
            }
            if (valid_array($have_shows)) {
                foreach ($have_shows as $have_show) {
                    if ($have_show['episode'] == $item['episode']) {
                        $tdata['have_show'] = $have_show;
                        $have_episodes[] = $item['episode'];
                        break;
                    }
                }
            }
            $episode_data .= $frontend->getTpl('episodes_rows', array_merge($item, $tdata));

            $item_counter++;
        }
    }
    $episode_data .= '</div>'; //EPISODE_CONTAINER
    $episode_data .= '</div>'; //TABLE
    $episode_data .= '<div class="episode_options">';
    $episode_list = '';
    $n_episodes = count($items_details);

    for ($a = 1; $a <= $n_episodes; $a++) {
        if (!in_array($a, $have_episodes)) {
            if ($a == $n_episodes) {
                $episode_list .= $a;
            } else {
                $episode_list .= $a . ',';
            }
        }
    }
    if (!empty($episode_list)) {
        $episode_data .= '<a class="episode_link" href="' . $iurl . '&wanted=1&season=' . $season . '&episode=' . $episode_list . '">' . $LNG['L_WANT_ALL'] . '</a>';
    }
    $episode_data .= '</div>';

    return $episode_data;
}

function get_follow_show($oid) {
    global $LNG, $db;
    $seasons = [];

    $stmt = $db->query('SELECT season,episode FROM shows_details WHERE themoviedb_id=' . $oid . '');
    $items = $db->fetchAll($stmt);

    foreach ($items as $item) {
        (!isset($seasons[$item['season']]) || $seasons[$item['season']] < $item['episode']) ? $seasons[$item['season']] = $item['episode'] : null;
    }
    $options = [];
    foreach ($seasons as $season => $episodes) {
        for ($i = 1; $i <= $episodes; $i++) {
            $s = 'S' . $season . 'E' . $i;
            $options[] = $s;
        }
    }
    $html = '<form class="inline" method="POST" action="?page=wanted">';
    $html .= '<input type="hidden" name="id" value="' . $oid . '" />';
    $html .= '<input class="action_link" type="submit" name="track_show" value="' . $LNG['L_FOLLOW_SHOW'] . '"/>';
    $html .= '<select name="track_show">';
    foreach ($options as $option) {
        $html .= '<option value="' . $option . '">>=' . $option . '</option>';
    }
    $html .= '</select>';
    $html .= '</form>';

    return $html;
}

function get_media_files($id, $media_type) {
    global $db, $LNG, $cfg;

    $sel_values = [];
    $media_files = '';
    $selected_id = Filter::postInt('selected_id');
    $library = 'library_' . $media_type;
    $files = $db->getItemsByField($library, 'master', $id);

    if (!valid_array($files)) {
        return false;
    }
    foreach ($files as $file) {
        if (empty($selected_id)) {
            $selected_id = $file['id'];
            $selected_item = $file;
        } else {
            if (empty($selected_item)) {
                if ($file['id'] == $selected_id) {
                    $selected_item = $file;
                }
            }
        }
        $sel_values[] = [
            'value' => $file['id'],
            'name' => basename($file['path'])
        ];
    }

    usort($sel_values, function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
    if (!empty($selected_item['path'])) {
        $mediainfo = mediainfo_formated($selected_item['path']);
        if (isset($mediainfo['General']['Duration'])) {
            $media_files .= html::span([], $LNG['L_DURATION'] . ': ');
            $media_files .= html::span([], format_seconds($mediainfo['General']['Duration']));
        }
        if (!empty($mediainfo)) {
            $media_files .= html::br([]);
            $media_files .= html_mediainfo_tags($mediainfo);
        }
    }

    $media_files .= Html::form(['method' => 'POST'], Html::select(['onChange' => 'this.form.submit()', 'selected' => $selected_id, 'name' => 'selected_id'], $sel_values));
    //Identify link
    $link_identify_parms = ['page' => 'identify', 'identify' => $selected_id, 'media_type' => $media_type];
    $media_files .= Html::link(['class' => 'action_link'], '', $LNG['L_IDENTIFY'], $link_identify_parms);
    if ($media_type == 'shows') {
        $link_identify_all_parms = ['page' => 'identify', 'identify_all' => $selected_id, 'media_type' => $media_type];
        $media_files .= Html::link(['class' => 'action_link'], '', $LNG['L_IDENTIFY_ALL'], $link_identify_all_parms);
    }

    //Delete reg
    $link_delete_parms = ['page' => 'view', 'id' => $id, 'deletereg_id' => $selected_id, 'view_type' => $media_type . '_library'];
    $media_files .= Html::link(['class' => 'action_link', 'onClick' => 'return confirm(\'Are you sure?\')'], '', $LNG['L_DELETE_REGISTER'], $link_delete_parms);

    if ($media_type == 'movies') {
        //localplayer
        if ($cfg['localplayer']) {
            $link_localplayer_parms = ['page' => 'localplayer', 'id' => $selected_id, 'media_type' => $media_type];
            $media_files .= Html::link(['class' => 'action_link'], '', 'LocalPlay', $link_localplayer_parms);
        }
        //Download
        if ($cfg['download_button']) {
            $link_download_parms = ['page' => 'download', 'id' => $selected_id, 'media_type' => $media_type, 'view_type' => 'movies_library'];
            $media_files .= Html::link(['class' => 'action_link'], '', $LNG['L_DOWNLOAD'], $link_download_parms);
        }
    }

    return $media_files;
}
