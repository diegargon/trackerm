<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function view() {
    global $cfg, $LNG, $db, $newdb, $filter;
    $type = $filter->getString('type');
    $id = $filter->getInt('id');

    empty($id) ? msg_box($msg = ['title' => $LNG['L_ERRORS'], 'body' => $LNG['L_ERR_BAD_ID']]) : null;

    $other = [];
    $page = '';

    if ($type == 'movies_library') {
        $t_type = 'biblio-movies';
        $other['reidentify'] = 1;
        $other['deletereg'] = 1;
    } else if ($type == 'shows_library') {
        $t_type = 'biblio-shows';
        $other['reidentify'] = 1;
        $other['deletereg'] = 1;
    } else if ($type == 'movies_torrent') {
        $t_type = 'jackett_movies';
    } else if ($type == 'shows_torrent') {
        $t_type = 'jackett_shows';
    } else if ($type == 'movies_db') {
        $other['wanted'] = 1;
        $t_type = 'tmdb_search';
    } else if ($type == 'shows_db') {
        $t_type = 'tmdb_search';
    } else {
        return false;
    }
    $other['page_type'] = $type;

    if ($t_type == 'tmdb_search') {
        $item = $newdb->getItemByID($t_type, $id);
    } else {
        $item = $db->getItemByID($t_type, $id);
    }
    empty($item) ? msg_box($msg = ['title' => $LNG['L_ERRORS'], 'body' => $LNG['L_ITEM_NOT_FOUND'] . '1A1003']) : null;

    if ($type == 'movies_db') {
        $library_item = $db->getItemByField('biblio-movies', 'themoviedb_id', $item['themoviedb_id']);
        if ($library_item !== false) {
            $item['in_library'] = $library_item['id'];
        }
    } else if ($type == 'shows_db') {
        $library_item = $db->getItemByField('biblio-shows', 'themoviedb_id', $item['themoviedb_id']);
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
        $shows_library = $db->getTableData('biblio-shows');

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



    if (!empty($item['poster']) && $cfg['CACHE_IMAGES']) {
        $cache_img_response = cacheImg($item['poster']);
        if ($cache_img_response !== false) {
            $item['poster'] = $cache_img_response;
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
        $stitle = trim($filter->getString('search_movie_db'));
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
        $torrent_results = search_movie_torrents($stitle);
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

    $page = $filter->getInt('page');
    $id = $filter->getInt('id');
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
        $extra .= search_shows_torrents($stitle);
    }

    return $extra;
}

function view_seasons($id, $update = false) {
    global $db, $LNG, $filter;

    $seasons_data = '';
    $episode_data = '';

    $item = $db->getItemByField('shows_details', 'themoviedb_id', $id);
    if (
            ($item === false) &&
            ( ($item = mediadb_getSeasons($id, $update)) === false )
    ) {
        return false;
    }

    if ($item !== false && $update !== false) {
        $item = mediadb_getSeasons($id, $update);
    }

    $wanted = $filter->getInt('wanted');
    $season = $filter->getInt('season');
    $episode = $filter->getInt('episode');

    if (!empty($wanted) && !empty($season) && !empty($episode)) {
        wanted_episode($id, $season, $episode);
    }
    $seasons_data .= '<span>Tº' . $LNG['L_SEASONS'] . ': ' . $item['n_seasons'] . '</span><br/>';
    $seasons_data .= '<span>Tº' . $LNG['L_EPISODES'] . ': ' . $item['n_episodes'] . '</span><br/>';
    $seasons = $item['seasons'];
    $iurl = basename($_SERVER['REQUEST_URI']);
    $iurl = preg_replace('/&season=\d{1,4}/', '', $iurl);
    $iurl = preg_replace('/&season=\d{1,4}/', '', $iurl);
    $iurl = preg_replace('/&episode=\d{1,4}/', '', $iurl);
    for ($i = 1; $i <= $item['n_seasons']; $i++) {
        if (!isset($seasons[$i]['episodes'])) {
            continue;
        }

        $seasons_data .= '<a class="season_link" href="' . $iurl . '&season=' . $i . '">' . $LNG['L_SEASON'] . ': ' . $i . '</a>';

        if (isset($season) && $season == $i) {
            $episode_data = '<div class="divTable">';
            $have_episodes = [];
            foreach ($seasons[$i]['episodes'] as $num => $episode_db) {
                $episode_data .= '<div class="divTableRow">';

                $have = check_if_have_show($id, $i, $num);
                $episode_data .= '<div class="divTableCellEpisodes">' . $num . '</div>';
                if ($have !== false) {
                    $have_episodes[] = $num;
                    $episode_data .= '<div class="divTableCellEpisodes" style="color:yellow;">' . $episode_db['title'] . '</div>';
                    $episode_data .= '<div class="divTableCellEpisodes">';
                    $episode_data .= '<a class="episode_link" href="?page=download&type=shows_library&id=' . $have['id'] . '">';
                    $episode_data .= $LNG['L_DOWNLOAD'];
                    $episode_data .= '</a></div>';
                } else {
                    $episode_data .= '<div class="divTableCellEpisodes">' . $episode_db['title'] . '</div>';
                    $episode_data .= '<div class="divTableCellEpisodes">';
                    $episode_data .= '<a class="episode_link" href="' . $iurl . '&wanted=1&season=' . $i . '&episode=' . $num . '">';
                    $episode_data .= $LNG['L_WANTED'];
                    $episode_data .= '</a>';
                    $episode_data .= '</div>';
                }
                $episode_data .= '</div>';
            }

            $episode_data .= '<div class="divTableRow">';
            $episode_data .= '<div class="divTableCellEpisodes"></div>';
            $episode_data .= '<div class="divTableCellEpisodes"></div>';
            $episode_data .= '<div class="divTableCellEpisodes">';
            $episode_list = '';
            $n_episodes = $seasons[$i]['n_episodes'];
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
                $episode_data .= '<a class="episode_link" href="' . $iurl . '&wanted=1&season=' . $i . '&episode=' . $episode_list . '">' . $LNG['L_WANT_ALL'] . '</a>';
            }
            $episode_data .= '</div></div></div>';
        }
    }
    $seasons_data .= '<br/>' . $episode_data;

    return $seasons_data;
}

function check_if_have_show($id, $season, $episode) {
    global $db;

    $shows = $db->getTableData('biblio-shows');
    //echo $id .':'. $season .':'. $episode .'<br>';
    foreach ($shows as $show) {
        if (
                isset($show['themoviedb_id']) &&
                isset($show['season']) &&
                isset($show['chapter']) &&
                $show['themoviedb_id'] == $id &&
                $show['season'] == $season &&
                $show['chapter'] == $episode
        ) {
            return $show;
        }
    }

    return false;
}
