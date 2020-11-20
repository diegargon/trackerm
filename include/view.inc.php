<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function view() {
    global $cfg, $LNG, $db;
    $type = $_GET['type'];
    $id = $_GET['id'];
    $other = [];
    $page = '';

    if ($type == 'movies_library') {
        $t_type = 'biblio-movies';
        $other['reidentify'] = 1;
    } else if ($type == 'shows_library') {
        $t_type = 'biblio-shows';
        $other['reidentify'] = 1;
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

    $item = $db->getItemByID($t_type, $id);

    if ($type == 'shows_library' || $type == 'shows_db') {
        $other['seasons_data'] = view_seasons($item['themoviedb_id']);
    }
    if (!empty($item['poster']) && $cfg['CACHE_IMAGES']) {
        $cache_img_response = get_and_cache_img($item['poster']);
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
    global $LNG;

    $extra = '';

    $extra .= '<form method="GET" action="">';
    $extra .= '<input type="hidden" name="page" value="' . $_GET['page'] . '"/>';
    $extra .= '<input type="hidden" name="id" value="' . $_GET['id'] . '"/>';
    $extra .= '<input type="hidden" name="type" value="' . $_GET['type'] . '"/>';
    $extra .= '<input class="submit_btn" type="submit" name="more_movies" value="' . $LNG['L_SEARCH_MOVIES'] . '" >';
    $extra .= '<input class="submit_btn" type="submit" name="more_torrents" value="' . $LNG['L_SHOW_TORRENTS'] . '" >';

    $title = getFileTitle($item['title']);

    if (!empty($_GET['search_movie_db'])) {
        $stitle = trim($_GET['search_movie_db']);
    } else {
        $stitle = $title;
    }
    $extra .= '<input type="text" name="search_movie_db" value="' . $stitle . '">';
    $extra .= '</form>';

    if (
            isset($_GET['more_movies']) || (!empty($opt['auto_show_db']) && !isset($_GET['more_torrents']))
    ) {
        $movies = db_search_movies($stitle);
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
    global $LNG;

    $extra = '';

    $extra .= '<form method="GET">';
    $extra .= '<input type="hidden" name="page" value="' . $_GET['page'] . '"/>';
    $extra .= '<input type="hidden" name="id" value="' . $_GET['id'] . '"/>';
    $extra .= '<input type="hidden" name="type" value="' . $_GET['type'] . '"/>';
    $extra .= '<input class="submit_btn" type="submit" name="more_shows" value="' . $LNG['L_SEARCH_SHOWS'] . '" >';
    $extra .= '<input class="submit_btn" type="submit" name="more_torrents" value="' . $LNG['L_SHOW_TORRENTS'] . '" >';

    $title = getFileTitle($item['title']);

    if (!empty($_GET['search_shows_db'])) {
        $stitle = trim($_GET['search_shows_db']);
    } else {
        $stitle = $title;
    }

    $extra .= '<input type="text" name="search_shows_db" value="' . $stitle . '">';
    $extra .= '</form>';

    if (
            isset($_GET['more_shows']) || (!empty($opt['auto_show_db']) && !isset($_GET['more_torrents']))
    ) {
        $shows = db_search_shows($stitle);
        !empty($shows) ? $extra .= buildTable('L_DB', $shows, $opt) : null;
    }

    if (
            isset($_GET['more_torrents']) || (!empty($opt['auto_show_torrents']) && !isseT($_GET['more_shows']))
    ) {
        $extra .= search_shows_torrents($stitle);
    }

    return $extra;
}

function view_seasons($id) {
    global $db, $LNG;

    $seasons_data = '';
    $episode_data = '';

    $item = $db->getItemByField('shows_details', 'themoviedb_id', $id);
    if (
            ($item === false) &&
            ( ($item = db_get_seasons($id)) === false )
    ) {
        return false;
    }

    if (!empty($_GET['wanted']) && !empty($_GET['season']) && !empty($_GET['episode'])) {
        wanted_episode($id, $_GET['season'], $_GET['episode']);
    }
    $seasons_data .= '<span>Nº' . $LNG['L_SEASONS'] . ': ' . $item['n_seasons'] . '</span><br/>';
    $seasons_data .= '<span>Nº' . $LNG['L_EPISODES'] . ': ' . $item['n_episodes'] . '</span><br/>';
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

        if (isset($_GET['season']) && $_GET['season'] == $i) {
            $episode_data = '<div class="divTable">';
            $have_episodes = [];
            foreach ($seasons[$i]['episodes'] as $num => $episode) {
                $episode_data .= '<div class="divTableRow">';

                $have = check_if_have_show($id, $i, $num);
                $episode_data .= '<div class="divTableCellEpisodes">' . $num . '</div>';
                if ($have !== false) {
                    $have_episodes[] = $num;
                    $episode_data .= '<div class="divTableCellEpisodes" style="color:yellow;">' . $episode['title'] . '</div>';
                } else {
                    $episode_data .= '<div class="divTableCellEpisodes">' . $episode['title'] . '</div>';
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
            $episode_data .= '<a class="episode_link" href="' . $iurl . '&wanted=1&season=' . $i . '&episode=' . $episode_list . '">' . $LNG['L_WANT_ALL'] . '</a>';
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
                $show['themoviedb_id'] == $id &&
                $show['season'] == $season &&
                $show['chapter'] == $episode
        ) {
            return $show;
        }
    }

    return false;
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

        $item = $db->getItembyField('tmdb_search', 'themoviedb_id', $id);
        $title_search = $item['title'] . ' S' . $season . 'E' . $episode;


        $wanted_item[$id]['id'] = $db->getLastID('wanted');
        $wanted_item[$id]['themoviedb_id'] = $item['themoviedb_id'];
        $wanted_item[$id]['title'] = $title_search;
        $wanted_item[$id]['qualitys'] = $cfg['TORRENT_QUALITYS_PREFS'];
        $wanted_item[$id]['ignores'] = $cfg['TORRENT_IGNORES_PREFS'];
        $wanted_item[$id]['added'] = time();
        $wanted_item[$id]['day_check'] = 'L_DAY_ALL';
        $wanted_item[$id]['type'] = 'shows';
        $wanted_item[$id]['season'] = $season;
        $wanted_item[$id]['episode'] = $episode;

        $db->addUniqElements('wanted', $wanted_item, 'title');
    }
}
