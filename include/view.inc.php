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
    global $cfg, $LNG, $db, $db, $filter;
    $type = $filter->getString('type');
    $id = $filter->getInt('id');

    empty($id) ? msg_box($msg = ['title' => $LNG['L_ERROR'], 'body' => $LNG['L_ERR_BAD_ID']]) : null;

    $other = [];
    $page = '';

    if ($type == 'movies_library') {
        $t_type = 'library_movies';
        $media_type = 'movies';
        $other['reidentify'] = 1;
        $other['deletereg'] = 1;
    } else if ($type == 'shows_library') {
        $t_type = 'library_shows';
        $media_type = 'shows';
        $other['reidentify'] = 1;
        $other['deletereg'] = 1;
    } else if ($type == 'movies_torrent') {
        $t_type = 'jackett_movies';
        $media_type = 'movies';
    } else if ($type == 'shows_torrent') {
        $t_type = 'jackett_shows';
        $media_type = 'shows';
    } else if ($type == 'movies_db') {
        $media_type = 'movies';
        $other['wanted'] = 1;
        $t_type = 'tmdb_search_movies';
    } else if ($type == 'shows_db') {
        $t_type = 'tmdb_search_shows';
        $media_type = 'shows';
    } else {
        return false;
    }
    $other['page_type'] = $type;


    $item = $db->getItemById($t_type, $id);

    empty($item) ? msg_box($msg = ['title' => $LNG['L_ERROR'], 'body' => $LNG['L_ITEM_NOT_FOUND'] . '1A1003']) : null;

    if ($type == 'movies_db') {
        $library_item = $db->getItemByField('library_movies', 'themoviedb_id', $item['themoviedb_id']);
        if ($library_item !== false) {
            $item['in_library'] = $library_item['id'];
        }
    } else if ($type == 'shows_db') {
        $library_item = $db->getItemByField('library_shows', 'themoviedb_id', $item['themoviedb_id']);
        if ($library_item !== false) {
            $item['in_library'] = $library_item['id'];
        }
    }

    if ($type == 'shows_library' || $type == 'shows_db') {
        if (isset($_GET['update'])) {
            $other['seasons_data'] = view_seasons($item['themoviedb_id'], true);
        } else {
            $other['seasons_data'] = view_seasons($item['themoviedb_id']);
        }
    }

    if (isset($item['size'])) {
        $item['size'] = human_filesize($item['size']);
    }

    if ($type == 'shows_library') {
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

    ($type == 'movies_db' || $type == 'shows_db') ? $opt['auto_show_torrents'] = 1 : null;
    ($type == 'movies_torrent' || $type == 'shows_torrent') ? $opt['auto_show_db'] = 1 : null;

    if ($type == 'movies_torrent' || $type == 'movies_db' || $type == 'movies_library') {
        $item['type'] = 'movies';
        $other['extra'] .= view_extra_movies($item, $opt);
    }

    if ($type == 'shows_torrent' || $type == 'shows_db' || $type == 'shows_library') {
        $item['type'] = 'shows';
        $other['extra'] .= view_extra_shows($item, $opt);
    }

    //TODO: To remove, fix a old databse bug than add too many sss
    if (!empty($item['guessed_trailer']) && substr(trim($item['guessed_trailer']), 0, 6) == 'httpss') {
        $item['guessed_trailer'] = preg_replace('/httpss(\w+):/', 'https:', $item['guessed_trailer']);
        if (!empty($item['ilink'])) {
            if ($item['ilink'] == 'movies_torrent') {
                $db->update('jackett_movies', ['guessed_trailer' => $item['guessed_trailer']], ['id' => ['value' => $item['id']]]);
            }
            if ($item['ilink'] == 'shows_torrent') {
                $db->update('jackett_shows', ['guessed_trailer' => $item['guessed_trailer']], ['id' => ['value' => $item['id']]]);
            }
        }
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
    $type = $filter->getString('type');


    $extra = '';

    $extra .= '<form method="GET" action="">';
    $extra .= '<input type="hidden" name="page" value="' . $page . '"/>';
    $extra .= '<input type="hidden" name="id" value="' . $id . '"/>';
    $extra .= '<input type="hidden" name="type" value="' . $type . '"/>';
    $extra .= '<input class="submit_btn" type="submit" name="more_movies" value="' . $LNG['L_SEARCH_MOVIES'] . '" >';
    $extra .= '<input class="submit_btn" type="submit" name="more_torrents" value="' . $LNG['L_SHOW_TORRENTS'] . '" >';

    $title = getFileTitle($item['title']);

    if (!empty($_GET['search_movie_db'])) {
        $stitle = trim($filter->getUtf8('search_movie_db'));
    } else {
        $stitle = $title;
    }

    $extra .= '<input type="text" name="search_movie_db" value="' . $stitle . '">';
    $extra .= '</form>';

    if (
            isset($_GET['more_movies']) || (!empty($opt['auto_show_db']) && !isset($_GET['more_torrents']))
    ) {
        $movies = mediadb_searchMovies($stitle);
        !empty($movies) ? $extra .= buildTable('L_DB', $movies, $opt) : null;
    }

    if (
            isset($_GET['more_torrents']) || (!empty($opt['auto_show_torrents']) && !isset($_GET['more_movies']))
    ) {
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
    $type = $filter->getString('type');

    $extra = '';

    $extra .= '<form method="GET">';
    $extra .= '<input type="hidden" name="page" value="' . $page . '"/>';
    $extra .= '<input type="hidden" name="id" value="' . $id . '"/>';
    $extra .= '<input type="hidden" name="type" value="' . $type . '"/>';
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

function view_seasons($id, $update = false) {
    //FIX REBUILD/MESSY
    global $db, $LNG, $filter, $cfg;

    $seasons_data = '';
    $episode_data = '';
    $wanted = $filter->getInt('wanted');
    $season = $filter->getInt('season');
    $episode = $filter->getInt('episode');

    if (empty($episode) && !empty($_GET['episode'])) {

        $episodes_check = explode(',', $_GET['episode']);

        if (count($episodes_check) > 0) {
            if ($filter->varInt($episodes_check)) {
                $episode = $_GET['episode'];
            }
        }
    }

    if (!empty($wanted) && !empty($season) && !empty($episode)) {
        wanted_episode($id, $season, $episode);
    }

    if (empty($season)) {
        $item = $db->getItemByField('shows_details', 'themoviedb_id', $id);
        if ($item === false || $update) {
            mediadb_getSeasons($id);
            $item = $db->getItemByField('shows_details', 'themoviedb_id', $id);
        }
    } else {
        $where['themoviedb_id'] = ['value' => $id];
        $where['season'] = ['value' => $season];

        $results = $db->select('shows_details', null, $where);
        $items = $db->fetchAll($results);

        if ($items === false || $update) {
            $items = mediadb_getSeasons($id);
        }
    }

    if (empty($item) && empty($items)) {
        return false;
    }
    if (!empty($item)) {
        $seasons = $item['seasons'];
        $episodes = $item['episodes'];
    } else {
        $seasons = $items[0]['seasons'];
        $episodes = $items[0]['episodes'];
    }
    $seasons_data .= '<span>' . $LNG['L_SEASONS'] . ': ' . $seasons . ' ' . $LNG['L_EPISODES'] . ': ' . $episodes . '</span><br/>';

    $iurl = basename($_SERVER['REQUEST_URI']);
    $iurl = preg_replace('/&season=\d{1,4}/', '', $iurl);
    $iurl = preg_replace('/&season=\d{1,4}/', '', $iurl);
    $iurl = preg_replace('/&episode=\d{1,4}/', '', $iurl);

    for ($i = 1; $i <= $seasons; $i++) {
        $seasons_data .= '<a class="season_link" href="' . $iurl . '&season=' . $i . '">' . $LNG['L_SEASON'] . ': ' . $i . '</a>';
    }

    $episode_data = '';
    if ($season) {
        $episode_data .= '<div class="episode_container">';
        $episode_data .= '<hr/><div class="divTable">';
        $have_episodes = [];

        $item_counter = 0;
        foreach ($items as $item) {
            //TODO MOVE TO TPL
            if ($item['season'] == $season) {
                if ($item_counter == 12) {
                    $episode_data .= '</div>'; //Table
                    $episode_data .= '</div>'; //Container
                    $episode_data .= '<div class="episode_container">';
                    $episode_data .= '<hr/><div class="divTable">';
                    $item_counter = 0;
                }
                $have = check_if_have_show($id, $item['season'], $item['episode']);

                $episode_data .= '<div class="divTableRow">';
                $episode_data .= '<div class="divTableCellEpisodes">' . $item['episode'] . '</div>';
                if ($have !== false) {
                    $have_episodes[] = $item['episode'];
                    $episode_data .= '<div class="divTableCellEpisodes" style="color:yellow;">' . $item['title'] . '</div>';
                    if (!empty($cfg['download_button'])) {
                        $episode_data .= '<div class="divTableCellEpisodes">';
                        $episode_data .= '<a class="episode_link" href="?page=download&type=shows_library&id=' . $have['id'] . '">' . $LNG['L_DOWNLOAD'] . '</a>';
                        if ($cfg['localplayer']) {
                            $episode_data .= '<a class="episode_link inline"  target=_blank href="?page=localplayer&id=' . $have['id'] . '&media_type=shows">' . $LNG['L_LOCALPLAYER'] . '</a>';
                        }
                        $episode_data .= '</div>';
                    }
                } else {
                    $episode_data .= '<div class="divTableCellEpisodes">' . $item['title'] . '</div>';
                    $episode_data .= '<div class="divTableCellEpisodes">';
                    $episode_data .= '<a class="episode_link" href="' . $iurl . '&wanted=1&season=' . $season . '&episode=' . $item['episode'] . '">';
                    $episode_data .= $LNG['L_WANTED'];
                    $episode_data .= '</a>';
                    $episode_data .= '</div>';
                }
                $episode_data .= '</div>';
                $item_counter++;
            }
        }
        $episode_data .= '</div>'; //EPISODE_CONTAINER
        $episode_data .= '</div>'; //TABLE

        $episode_data .= '<div class="episode_options">';
        $episode_list = '';
        $n_episodes = count($items);

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
    }

    $seasons_data .= '<br/>' . $episode_data;
    return $seasons_data;
}

function check_if_have_show($id, $season, $episode) {
    global $db;

    if (!is_numeric($id) || !is_numeric($season) || !is_numeric($episode)) {
        return false;
    }

    $where['themoviedb_id'] = ['value' => $id];
    $where['season'] = ['value' => $season];
    $results = $db->select('library_shows', null, $where);
    $season_episodes = $db->fetchAll($results);

    foreach ($season_episodes as $s_episode) {

        if (
                isset($s_episode['themoviedb_id']) &&
                isset($s_episode['season']) &&
                isset($s_episode['episode']) &&
                $s_episode['themoviedb_id'] == $id &&
                $s_episode['season'] == $season &&
                $s_episode['episode'] == $episode
        ) {

            return $s_episode;
        }
    }
    return false;
}
