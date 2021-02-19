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
    global $cfg, $LNG, $db, $filter;
    $view_type = $filter->getString('type');
    $id = $filter->getInt('id');

    empty($id) ? msg_box($msg = ['title' => $LNG['L_ERROR'], 'body' => $LNG['L_ERR_BAD_ID']]) : null;

    $other = [];
    $page = '';

    if ($view_type == 'movies_library') {
        $table = 'library_movies';
        $media_type = 'movies';
        $other['reidentify'] = 1;
        $other['deletereg'] = 1;
    } else if ($view_type == 'shows_library') {
        $table = 'library_shows';
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
    $other['page_type'] = $view_type;
    $item = $db->getItemById($table, $id);

    if (empty($item)) {
        return msg_box($msg = ['title' => $LNG['L_ERROR'], 'body' => $LNG['L_ITEM_NOT_FOUND'] . ' 1A1003']);
    }

    if ($view_type == 'movies_db') {
        $library_item = $db->getItemByField('library_movies', 'themoviedb_id', $item['themoviedb_id']);
        if ($library_item !== false) {
            $item['in_library'] = $library_item['id'];
        }
    } else if ($view_type == 'shows_db') {
        $library_item = $db->getItemByField('library_shows', 'themoviedb_id', $item['themoviedb_id']);
        if ($library_item !== false) {
            $item['in_library'] = $library_item['id'];
        }
    }

    if ($view_type == 'shows_library' || $view_type == 'shows_db') {
        if (isset($_GET['update'])) {
            $other['seasons_data'] = view_seasons($item, true);
        } else {
            $other['seasons_data'] = view_seasons($item);
        }
    }

    (isset($item['size'])) ? $item['size'] = human_filesize($item['size']) : null;

    if ($view_type == 'shows_library') {
        $shows_library = $db->getTableData('library_shows');
        $i = 0;
        $tsize = 0;

        foreach ($shows_library as $show_library) {
            if (isset($show_library['themoviedb_id']) && $item['themoviedb_id'] &&
                    ($show_library['themoviedb_id'] == $item['themoviedb_id'])
            ) {
                $tsize = $show_library['size'] + $tsize;
                $i++;
            }
        }
        $item['have_episodes'] = $i;
        $item['size'] = human_filesize($tsize);
    }

    if (!empty($item['poster']) && $cfg['cache_images']) {
        $cache_img_response = cacheImg($item['poster']);
        if ($cache_img_response !== false) {
            $item['poster'] = $cache_img_response;
        }
    }

    if (empty($item['poster'])) {
        $item['poster'] = $cfg['img_url'] . '/not_available.jpg';
        empty($item['media_type']) ? $item['media_type'] = $media_type : null;

        $poster = mediadb_guessPoster($item);
        if (!empty($poster)) {
            if ($cfg['cache_images']) {
                $cache_img_response = cacheImg($poster);
                if ($cache_img_response !== false) {
                    $item['poster'] = $cache_img_response;
                }
            }
            $item['guessed_poster'] = 1;
        }
    }
    $other['extra'] = '';
    $opt['auto_show_torrents'] = 0;
    $opt['auto_show_db'] = 0;

    ($view_type == 'movies_db' || $view_type == 'shows_db') ? $opt['auto_show_torrents'] = 1 : null;
    ($view_type == 'movies_torrent' || $view_type == 'shows_torrent') ? $opt['auto_show_db'] = 1 : null;

    if ($view_type == 'movies_torrent' || $view_type == 'movies_db' || $view_type == 'movies_library') {
        $item['type'] = 'movies';
        $other['extra'] .= view_extra_movies($item, $opt);
    }

    if ($view_type == 'shows_torrent' || $view_type == 'shows_db' || $view_type == 'shows_library') {
        $item['type'] = 'shows';
        $other['extra'] .= view_extra_shows($item, $opt);
    }

    if ($view_type == 'shows_db' || $view_type == 'shows_library') {
        //NEWFEATURE
        $other['follow_show'] = get_follow_show($item['themoviedb_id']);
    }

    if (!empty($item['path'])) {
        $other['mediainfo'] = mediainfo_formated($item['path']);
        isset($other['mediainfo']['General']['Duration']) ? $other['mediainfo']['General']['Duration'] = format_seconds($other['mediainfo']['General']['Duration']) : null;
        isset($other['mediainfo']['General']['FrameRate']) ? $other['mediainfo']['General']['FrameRate'] = round($other['mediainfo']['General']['FrameRate']) . 'fps' : null;
        isset($other['mediainfo']['Audio'][1]['BitRate']) ? $other['mediainfo']['Audio'][1]['BitRate'] = substr($other['mediainfo']['Audio'][1]['BitRate'], 0, 2) . 'hz' : null;
        !empty($other['mediainfo']) ? $other['mediainfo_tags'] = html_mediainfo_tags($other['mediainfo']) : null;
    }
    $page = getTpl('view', array_merge($cfg, $LNG, $item, $other));

    return $page;
}

function view_extra_movies($item, $opt = null) {
    global $LNG, $filter;
    $id = $filter->getInt('id');
    $page = $filter->getString('page');
    $view_type = $filter->getString('type');

    $extra = '';
    $extra .= '<form method="GET" action="">';
    $extra .= '<input type="hidden" name="page" value="' . $page . '"/>';
    $extra .= '<input type="hidden" name="id" value="' . $id . '"/>';
    $extra .= '<input type="hidden" name="type" value="' . $view_type . '"/>';
    $extra .= '<input class="submit_btn" type="submit" name="more_movies" value="' . $LNG['L_SEARCH_MOVIES'] . '" >';
    $extra .= '<input class="submit_btn" type="submit" name="more_torrents" value="' . $LNG['L_SHOW_TORRENTS'] . '" >';

    $title = getFileTitle($item['title']);

    (!empty($_GET['search_movie_db'])) ? $stitle = trim($filter->getUtf8('search_movie_db')) : $stitle = $title;

    $extra .= '<input type="text" name="search_movie_db" value="' . $stitle . '">';
    $extra .= '</form>';

    if (isset($_GET['more_movies']) || (!empty($opt['auto_show_db']) && !isset($_GET['more_torrents']))) {
        $movies = mediadb_searchMovies($stitle);
        !empty($movies) ? $extra .= buildTable('L_DB', $movies, $opt) : null;
    }

    if (isset($_GET['more_torrents']) || (!empty($opt['auto_show_torrents']) && !isset($_GET['more_movies']))) {
        $search['words'] = $stitle;
        $torrent_results = search_media_torrents('movies', $search);
        if ($torrent_results !== false) {
            $extra .= $torrent_results;
        } else {
            $box_msg['title'] = $LNG['L_ERROR'] . ':' . $LNG['L_TORRENT'];
            $box_msg['body'] = $LNG['L_NOTHING_FOUND'];
            $extra .= msg_box($box_msg);
        }
    }

    return $extra;
}

function view_extra_shows($item, $opt) {
    global $LNG, $filter;

    $id = $filter->getInt('id');
    $page = $filter->getString('page');
    $view_type = $filter->getString('type');

    $extra = '';
    $extra .= '<form method="GET">';
    $extra .= '<input type="hidden" name="page" value="' . $page . '"/>';
    $extra .= '<input type="hidden" name="id" value="' . $id . '"/>';
    $extra .= '<input type="hidden" name="type" value="' . $view_type . '"/>';
    $extra .= '<input class="submit_btn" type="submit" name="more_shows" value="' . $LNG['L_SEARCH_SHOWS'] . '" >';
    $extra .= '<input class="submit_btn" type="submit" name="more_torrents" value="' . $LNG['L_SHOW_TORRENTS'] . '" >';

    $title = getFileTitle($item['title']);

    if (!empty($_GET['search_shows_db'])) {
        $stitle = trim($filter->getString('search_shows_db'));
    } else {
        $stitle = $title;
    }

    $extra .= '<input type="text" name="search_shows_db" value="' . $stitle . '">';
    $extra .= '</form>';

    if (
            isset($_GET['more_shows']) || (!empty($opt['auto_show_db']) && !isset($_GET['more_torrents']))
    ) {
        $shows = mediadb_searchShows($stitle);
        !empty($shows) ? $extra .= buildTable('L_DB', $shows, $opt) : null;
    }

    if (
            isset($_GET['more_torrents']) || (!empty($opt['auto_show_torrents']) && !isset($_GET['more_shows']))
    ) {
        $search['words'] = $stitle;
        $extra .= search_media_torrents('shows', $search);
    }

    return $extra;
}

function view_seasons($item, $update = false) {
    global $db, $LNG, $filter;

    $seasons_data = '';
    $episode_data = '';
    $id = $filter->getInt('id');
    $season = $filter->getInt('season');
    $view_type = $filter->getString('type');

    //SUBMIT WANTED (episode=1 || episode=1,2,3
    if ($filter->getInt('wanted')) {
        $episode = $filter->getInt('episode');

        if (empty($episode) && !empty($_GET['episode'])) {
            $episodes_check = explode(',', $_GET['episode']);

            if (valid_array($episodes_check)) {
                if ($filter->varInt($episodes_check)) {
                    $episode = $_GET['episode'];
                }
            }
        }

        if (!empty($season) && !empty($episode)) {
            wanted_episode($item['themoviedb_id'], $season, $episode);
        }
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

    $iurl = '?page=view&id=' . $id . '&type=' . $view_type;

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
    global $cfg, $LNG, $filter;

    $id = $filter->getInt('id');
    $view_type = $filter->getString('type');
    $iurl = '?page=view&id=' . $id . '&type=' . $view_type;

    $episode_data = '<div class="episode_container">';
    $episode_data .= '<hr/><div class="divTable">';
    $have_episodes = [];

    $item_counter = 0;
    foreach ($items_details as $item) {
        $tdata = [];
        $tdata['iurl'] = $iurl;
        if ($item['season'] == $season) {
            if ($item_counter == 12) {
                $episode_data .= '</div>'; //Table
                $episode_data .= '</div>'; //Container
                $episode_data .= '<div class="episode_container">';
                $episode_data .= '<hr/><div class="divTable">';
                $item_counter = 0;
            }
            $have_show = have_show($item['themoviedb_id'], $item['season'], $item['episode']);
            if (valid_array($have_show)) {
                $tdata['have_show'] = $have_show;
                $have_episodes[] = $item['episode'];
            }
            $episode_data .= getTpl('episodes_rows', array_merge($item, $tdata, $LNG, $cfg));

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

function have_show($oid, $season, $episode) {
    global $db;

    if (!is_numeric($oid) || !is_numeric($season) || !is_numeric($episode)) {
        return false;
    }

    $where['themoviedb_id'] = ['value' => $oid];
    $where['season'] = ['value' => $season];
    $where['episode'] = ['value' => $episode];

    $results = $db->select('library_shows', null, $where, 'LIMIT 1');
    $show = $db->fetchAll($results);

    return valid_array($show) ? $show[0] : false;
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
