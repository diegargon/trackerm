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
    global $db, $user;

    $view_tpl['templates'] = [];
    $other = $view_media = [];

    $view_type = Filter::getString('view_type');
    $id = Filter::getInt('id');
    $selected_id = Filter::postInt('selected_id');

    $other['selected_id'] = $selected_id;

    if ($view_type == 'movies_library') {
        $table = 'library_master_movies';
        $media_type = 'movies';
        $other['reidentify'] = 1;
        if ($user->isAdmin()) {
            $other['deletereg'] = 1;
            $other['custom_poster_btn'] = 1;
        }
    } else if ($view_type == 'shows_library') {
        $table = 'library_master_shows';
        $media_type = 'shows';
        $other['reidentify'] = 1;
        if (!empty(Filter::getInt('season'))) {
            $other['season'] = Filter::getInt('season');
        } else {
            $other['season'] = 1;
        }
        if ($user->isAdmin()) {
            $other['deletereg'] = 1;
            $other['custom_poster_btn'] = 1;
        }
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
    !empty($_GET['show_custom_poster']) && $user->isAdmin() ? $other['show_custom_poster'] = 1 : null;

    $other['view_type'] = $view_type;
    $item = $db->getItemById($table, $id);

    if (empty($item)) {
        return ['msg_type' => 1, 'title' => 'L_ERROR', 'body' => 'L_ITEM_NOT_FOUND'];
    }

    if ($view_type == 'movies_db' || $view_type == 'shows_db') {
        //TODO master no tmdbid
        $in_library = $db->getItemByField('library_master_' . $media_type, 'themoviedb_id', $item['themoviedb_id']);
        if ($in_library !== false) {
            $item['in_library'] = $in_library['id'];
        }
    }
    !empty($item['total_size']) ? $item['total_size'] = human_filesize($item['total_size']) : null;

    if (!empty($_POST['change_custom_poster']) && $user->isAdmin()) {
        if (empty($_POST['new_custom_poster'])) {
            $new_poster = '';
        } else {
            $new_poster = Filter::postUrl('new_custom_poster');
        }
        $db->updateItemById('library_master_' . $media_type, $id, ['custom_poster' => $new_poster]);
        $item['custom_poster'] = $new_poster;
    }

    $item['poster'] = get_poster($item);

    $other['extra'] = '';
    $opt['auto_show_torrents'] = 0;
    $opt['auto_show_db'] = 0;

    ($view_type == 'movies_db' || $view_type == 'shows_db') ? $opt['auto_show_torrents'] = 1 : null;
    ($view_type == 'movies_torrent' || $view_type == 'shows_torrent') ? $opt['auto_show_db'] = 1 : null;

    //add template media files selector
    if (in_array($view_type, ['movies_library', 'shows_library'])) {
        $media_file_tpl = get_media_files($item, $media_type, $view_media);
        if (empty($other['selected_id']) && !empty($media_file_tpl['tpl_vars'][0]['file_id'])) {
            $other['selected_id'] = $media_file_tpl['tpl_vars'][0]['file_id'];
        }
        $view_tpl['templates'][] = $media_file_tpl;
    }
    if (in_array($view_type, ['movies_library', 'shows_library', 'movies_db', 'shows_db'])) {
        $where_view_media = [
            'uid' => ['value' => $user->getId()],
            'media_type' => ['value' => $media_type],
            'themoviedb_id' => ['value' => $item['themoviedb_id']],
        ];

        $results = $db->select('view_media', '*', $where_view_media);
        $view_media = $db->fetchAll($results);

        $opt['view_media'] = $view_media;

        $item['f_genres'] = get_fgenres($media_type, $item);
        $item['f_cast'] = get_fnames('cast', $media_type, $item);
        $item['f_writer'] = get_fnames('writer', $media_type, $item);
        $item['f_director'] = get_fnames('director', $media_type, $item);
    }

    if ($view_type == 'shows_library' || $view_type == 'shows_db') {
        isset($_GET['update']) ? $update = true : $update = false;
        $seasons_tpl = view_seasons($item, $opt, $update);

        if (!empty($seasons_tpl) && is_array($seasons_tpl['templates'])) {
            $view_tpl['templates'] = array_merge($view_tpl['templates'], $seasons_tpl['templates']);
        }
    }

    if ($view_type == 'movies_torrent' || $view_type == 'movies_db' || $view_type == 'movies_library') {
        empty($item['media_type']) ? $item['media_type'] = 'movies' : null;
        $extra_movies_tpl = view_extra_movies($item, $opt);
        $view_tpl['templates'] = array_merge($view_tpl['templates'], $extra_movies_tpl['templates']);
    }

    if ($view_type == 'shows_torrent' || $view_type == 'shows_db' || $view_type == 'shows_library') {
        empty($item['media_type']) ? $item['media_type'] = 'shows' : null;
        $extra_shows_tpl = view_extra_shows($item, $opt);
        $view_tpl['templates'] = array_merge($view_tpl['templates'], $extra_shows_tpl['templates']);
    }

    if ($view_type == 'shows_db' || $view_type == 'shows_library') {
        $item['follow_show'] = get_follow_show($item['themoviedb_id']);
    }

    if (!empty($item['collection'])) {
        $item['f_collection'] = get_fcollection($media_type, $item);
    }

    $view_tpl = [
        'name' => 'view',
        'tpl_file' => 'view',
        'tpl_pri' => 0,
        'tpl_vars' => array_merge($item, $other),
    ];

    $view_tpl['templates'][] = $view_tpl;

    return $view_tpl;
}

function view_extra_movies($item, $opt = null) {
    global $LNG, $prefs;

    $id = Filter::getInt('id');
    $page = Filter::getString('page');
    $view_type = Filter::getString('view_type');
    $title = getFileTitle($item['title']);
    (!empty($_GET['search_movies_db'])) ? $stitle = trim(Filter::getUtf8('search_movies_db')) : $stitle = $title;
    $stitle = preg_replace('/\s\d{4}/', '', $stitle);

    $columns = $prefs->getPrefsItem('tresults_columns');
    $rows = $prefs->getPrefsItem('tresults_rows');
    $items_per_page = $columns * $rows;

    $npage = Filter::getInt('npage');
    empty($npage) ? $npage = 1 : null;

    if ($npage > 1) {
        $jump_x_entrys = ($items_per_page * $npage) - $items_per_page;
    } else {
        $jump_x_entrys = 0;
    }

    $extra_movies['templates'] = [];

    $extra_movies['templates'][] = [
        'name' => 'view_extra_movies',
        'tpl_file' => 'view_extra_movies',
        'tpl_pri' => 10,
        'tpl_place' => 'view',
        'tpl_place_var' => 'extra',
        'tpl_vars' => [
            'page' => $page,
            'id' => $id,
            'view_type' => $view_type,
            'stitle' => $stitle,
        ]
    ];

    if (isset($_GET['more_movies']) || (!empty($opt['auto_show_db']) && !isset($_GET['more_torrents']))) {
        $movies = mediadb_searchMovies($stitle);
        if (!empty($movies)) {
            $opt['view_type'] = 'movies_db';
            $opt['head'] = $LNG['L_DB'];
            $opt['media_type'] = 'movies';

            $opt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');
            $pager_opt['num_table_objs'] = count($movies);
            $pager_opt['npage'] = $npage;
            $pager_opt['page'] = $page;
            $pager_opt['media_type'] = 'movies';
            $pager_opt['pager_place'] = 'extra_movies_container';
            $pager_opt['link_options'] = [
                'id' => $id,
                'view_type' => $view_type,
                'more_movies' => 1,
                'search_movies_db' => $stitle
            ];
            $extra_movies ['templates'][] = get_pager($pager_opt);

            $movies = array_slice($movies, $jump_x_entrys, $items_per_page);

            $extra_movies['templates'][] = [
                'name' => 'extra_movies',
                'tpl_file' => 'items_table',
                'tpl_pri' => 8,
                'tpl_place' => 'extra_movies_container',
                'tpl_place_var' => 'items',
                'tpl_vars' => $movies,
                'tpl_common_vars' => $opt,
            ];
            $extra_movies['templates'][] = [
                'name' => 'extra_movies_container',
                'tpl_file' => 'items_table_container',
                'tpl_pri' => 5,
                'tpl_place' => 'view',
                'tpl_place_var' => 'extra',
                'tpl_vars' => [
                    'head' => $LNG['L_DB'],
                ]
            ];
        }
    }

    if (isset($_GET['more_torrents']) || (!empty($opt['auto_show_torrents']) && !isset($_GET['more_movies']))) {
        $search['words'] = $stitle;
        $m_results = search_media_torrents('movies', $search, 'L_TORRENT');
        if (valid_array($m_results)) {
            $m_results = mix_media_res($m_results);
            if (!empty($m_results)) {
                $opt['view_type'] = 'movies_torrent';
                $opt['media_type'] = 'movies';
                $opt['num_table_objs'] = count($m_results);

                $opt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');

                $pager_opt['num_table_objs'] = count($m_results);
                $pager_opt['npage'] = $npage;
                $pager_opt['page'] = $page;
                $pager_opt['media_type'] = 'movies';
                $pager_opt['pager_place'] = 'extra_movies_container';
                $pager_opt['link_options'] = [
                    'id' => $id,
                    'view_type' => $view_type,
                    'more_torrents' => 1,
                    'search_movies_db' => $stitle
                ];
                $extra_movies ['templates'][] = get_pager($pager_opt);
                $m_results = array_slice($m_results, $jump_x_entrys, $items_per_page);

                $extra_movies['templates'][] = [
                    'name' => 'extra_movies',
                    'tpl_file' => 'items_table',
                    'tpl_pri' => 8,
                    'tpl_place' => 'extra_movies_container',
                    'tpl_place_var' => 'items',
                    'tpl_vars' => $m_results,
                    'tpl_common_vars' => $opt,
                ];
                $extra_movies['templates'][] = [
                    'name' => 'extra_movies_container',
                    'tpl_file' => 'items_table_container',
                    'tpl_pri' => 5,
                    'tpl_place' => 'view',
                    'tpl_place_var' => 'extra',
                    'tpl_vars' => [
                        'head' => $LNG['L_TORRENT'],
                    ]
                ];
            }
        } else {
            $extra_movies['templates'][] = [
                'name' => 'msgbox',
                'tpl_file' => 'msgbox',
                'tpl_pri' => 4,
                'tpl_place' => 'view',
                'tpl_place_var' => 'extra',
                'tpl_vars' => [
                    'title' => $LNG['L_TORRENT'],
                    'body' => $LNG['L_NOTHING_FOUND'],
                ]
            ];
        }
    }


    return $extra_movies;
}

function view_extra_shows($item, $opt) {
    global $prefs, $LNG;

    $id = Filter::getInt('id');
    $page = Filter::getString('page');
    $view_type = Filter::getString('view_type');
    $title = getFileTitle($item['title']);
    (!empty($_GET['search_shows_db'])) ? $stitle = trim(Filter::getString('search_shows_db')) : $stitle = $title;
    $npage = Filter::getInt('npage');
    empty($npage) ? $npage = 1 : null;

    $columns = $prefs->getPrefsItem('tresults_columns');
    $rows = $prefs->getPrefsItem('tresults_rows');
    $items_per_page = $columns * $rows;

    if ($npage > 1) {
        $jump_x_entrys = ($items_per_page * $npage) - $items_per_page;
    } else {
        $jump_x_entrys = 0;
    }

    $extra_shows['templates'] = [];

    $extra_shows['templates'][] = [
        'name' => 'view_extra_shows',
        'tpl_file' => 'view_extra_shows',
        'tpl_pri' => 10,
        'tpl_place' => 'view',
        'tpl_place_var' => 'extra',
        'tpl_vars' => [
            'page' => $page,
            'id' => $id,
            'view_type' => $view_type,
            'stitle' => $stitle,
        ]
    ];

    if (isset($_GET['more_shows']) || (!empty($opt['auto_show_db']) && !isset($_GET['more_torrents']))) {
        $shows = mediadb_searchShows($stitle);
        if (!empty($shows)) {
            $opt['view_type'] = 'shows_db';
            $opt['media_type'] = 'shows';
            $opt['head'] = $LNG['L_DB'];
            $opt['num_table_objs'] = count($shows);
            $opt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');

            $pager_opt['num_table_objs'] = count($shows);
            $pager_opt['npage'] = $npage;
            $pager_opt['page'] = $page;
            $pager_opt['media_type'] = 'shows';
            $pager_opt['pager_place'] = 'extra_shows_container';
            $pager_opt['link_options'] = [
                'id' => $id,
                'view_type' => $view_type,
                'more_shows' => 1,
                'search_shows_db' => $stitle
            ];
            $extra_shows['templates'][] = get_pager($pager_opt);

            $shows = array_slice($shows, $jump_x_entrys, $items_per_page);

            $extra_shows['templates'][] = [
                'name' => 'extra_shows',
                'tpl_file' => 'items_table',
                'tpl_pri' => 8,
                'tpl_place' => 'extra_shows_container',
                'tpl_place_var' => 'items',
                'tpl_vars' => $shows,
                'tpl_common_vars' => $opt,
            ];
            $extra_shows['templates'][] = [
                'name' => 'extra_shows_container',
                'tpl_file' => 'items_table_container',
                'tpl_pri' => 5,
                'tpl_place' => 'view',
                'tpl_place_var' => 'extra',
                'tpl_vars' => [
                    'head' => $LNG['L_DB'],
                ]
            ];
        }
    }

    if (isset($_GET['more_torrents']) || (!empty($opt['auto_show_torrents']) && !isset($_GET['more_shows']))) {
        $search['words'] = $stitle;

        $m_results = search_media_torrents('shows', $search);
        if (valid_array($m_results)) {
            $m_results = mix_media_res($m_results);
            if (!empty($m_results)) {
                $opt['view_type'] = 'shows_torrent';
                $opt['media_type'] = 'shows';
                $opt['more_torrents'] = 1;
                $opt['num_table_objs'] = count($m_results);
                $opt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');
                $pager_opt['num_table_objs'] = count($m_results);
                $pager_opt['npage'] = $npage;
                $pager_opt['page'] = $page;
                $pager_opt['media_type'] = 'shows';
                $pager_opt['pager_place'] = 'extra_shows_container';
                $pager_opt['link_options'] = [
                    'id' => $id,
                    'view_type' => $view_type,
                    'more_torrents' => 1,
                    'search_shows_db' => $stitle
                ];
                $extra_shows ['templates'][] = get_pager($pager_opt);
                $m_results = array_slice($m_results, $jump_x_entrys, $items_per_page);

                $extra_shows['templates'][] = [
                    'name' => 'extra_shows',
                    'tpl_file' => 'items_table',
                    'tpl_pri' => 8,
                    'tpl_place' => 'extra_shows_container',
                    'tpl_place_var' => 'items',
                    'tpl_vars' => $m_results,
                    'tpl_common_vars' => $opt,
                ];
                $extra_shows['templates'][] = [
                    'name' => 'extra_shows_container',
                    'tpl_file' => 'items_table_container',
                    'tpl_pri' => 5,
                    'tpl_place' => 'view',
                    'tpl_place_var' => 'extra',
                    'tpl_vars' => [
                        'head' => $LNG['L_TORRENT'],
                    ]
                ];
            }
        } else {
            $extra_shows['templates'][] = [
                'name' => 'msgbox',
                'tpl_file' => 'msgbox',
                'tpl_pri' => 4,
                'tpl_place' => 'view',
                'tpl_place_var' => 'extra',
                'tpl_vars' => [
                    'title' => $LNG['L_TORRENT'],
                    'body' => $LNG['L_NOTHING_FOUND'],
                ]
            ];
        }
    }


    return $extra_shows;
}

function view_seasons($item, $opt, $update = false) {
    global $db;

    $id = Filter::getInt('id');
    $season_ask = Filter::getInt('season');
    $view_type = Filter::getString('view_type');
    $seasons_tpl['templates'] = [];

    //SUBMITED WANTED (episode=1 || episode=1,2,3
    if (Filter::getInt('wanted') && !empty($season_ask)) {
        $episode = Filter::getInt('episode');

        if (empty($episode) && !empty($_GET['episode'])) {
            $episodes_check = explode(',', $_GET['episode']);

            if (valid_array($episodes_check) && Filter::varInt($episodes_check)) {
                $episode = Filter::getString('episode'); //episode string: "1,2,3..."
            }
        }
        !empty($episode) ? wanted_episode($item['themoviedb_id'], $season_ask, $episode) : null;
    }

    if (empty($season_ask)) {
        /*
         * by default (not ask for season details), we only need the fields seasons&episodes, that mean one
         *  item is enough but if we need request we get all season info and show season 1
         * TODO: check if is need in show_details seasons&episodes, this must be in "Season data" not need in every
         * entry in details
         */
        $item_details = $db->getItemByField('shows_details', 'themoviedb_id', $item['themoviedb_id']);
        if ($item_details === false || $update) {
            $items_details = mediadb_getSeasons($item['themoviedb_id']);
            $season_ask = 1;
        }
    } else {
        $where['themoviedb_id'] = ['value' => $item['themoviedb_id']];
        $where['season'] = ['value' => $season_ask];
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


    $seasons_tpl['templates'][] = [
        'name' => 'seasons_head',
        'tpl_file' => 'seasons_head',
        'tpl_pri' => 10,
        'tpl_place' => 'view',
        'tpl_place_var' => 'before_trailer',
        'tpl_vars' => [
            'id' => $id,
            'view_type' => $view_type,
            'seasons' => $seasons,
            'episodes' => $episodes,
        ]
    ];

    !empty($opt['view_media']) ? $view_media = $opt['view_media'] : $view_media = null;
    if (!empty($season_ask)) {

        $episode_tpl = view_season_detailed($season_ask, $items_details, $view_media);

        $seasons_tpl['templates'][] = $episode_tpl;
    }


    return $seasons_tpl;
}

function view_season_detailed($season_ask, $items_details, $view_media) {

    $id = Filter::getInt('id');
    $episode_data = '<div class="episode_container">';
    $episode_data .= '<hr/><div class="divTable">';
    $have_episodes = $episode_tpl = [];

    $have_shows = get_have_shows_season($items_details[0]['themoviedb_id'], $items_details[0]['season']);
    foreach ($items_details as $item_key => &$item) {
        $item['viewed'] = 0;
        $item['master_id'] = $id;
        if ($item['season'] != $season_ask) {
            unset($items_details[$item_key]);
            continue;
        }
        if (valid_array($have_shows)) {
            foreach ($have_shows as $have_show) {
                if ($have_show['episode'] == $item['episode']) {
                    $have_episodes[] = $item['episode']; //Used later to  add all/missing button
                    $item['have_show'] = $have_show;
                    if (valid_array($view_media)) {
                        foreach ($view_media as $view_media_item) {
                            if ($view_media_item['file_hash'] == $have_show['file_hash']) {
                                $item['viewed'] = 1;
                                break;
                            }
                        }
                    }

                    break;
                }
            }
        }
    }

    //var_dump($items_details);
    $missing_episodes = '';
    $n_episodes = count($items_details);

    for ($a = 1; $a <= $n_episodes; $a++) {
        if (!in_array($a, $have_episodes)) {
            if ($a == $n_episodes) {
                $missing_episodes .= $a;
            } else {
                $missing_episodes .= $a . ',';
            }
        }
    }

    $episode_tpl = [
        'name' => 'episodes_list',
        'tpl_file' => 'episodes_list',
        'tpl_pri' => 8,
        'tpl_place' => 'view',
        'tpl_place_var' => 'before_trailer',
        'tpl_vars' => $items_details,
        'tpl_common_vars' => ['missing_episodes' => $missing_episodes]
    ];

    return $episode_tpl;
}

function get_follow_show($oid) {
    global $db;
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

    $follow_show = [
        'oid' => $oid,
        'options' => $options,
    ];
    return $follow_show;
}

function get_media_files(&$item, $media_type, $view_media) {
    global $db, $cfg;

    $sel_values = [];
    $selected_id = Filter::postInt('selected_id');
    $library = 'library_' . $media_type;
    $files = $db->getItemsByField($library, 'master', $item['id']);
    $selected_item = '';

    if (!valid_array($files)) {
        return false;
    }

    foreach ($files as $file) {
        $file_name = '';

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
        $found_view = 0;
        $already_view_file = 0;
        foreach ($view_media as $view_media_item) {
            if ($view_media_item['file_hash'] == $file['file_hash']) {
                $found_view = 1;
                if ($selected_id == $file['id']) {
                    $already_view_file = 1;
                }
                break;
            }
        }

        $item['already_view_file'] = $already_view_file;
        $file_name .= basename($file['path']);
        ($found_view) ? $view_mark = 1 : $view_mark = 0;
        (is_link($file['path'])) ? $is_link = 1 : $is_link = 0;

        $sel_values[] = [
            'id' => $item['id'],
            'file_id' => $file['id'],
            'name' => $file_name,
            'view_mark' => $view_mark,
            'is_link' => $is_link,
        ];
    }

    usort($sel_values, function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    if (!empty($selected_item['path'])) {
        $mediainfo = mediainfo_formated($selected_item['path']);
        if (isset($mediainfo['General']['Duration'])) {
            $item['mediainfo_file_duration'] = format_seconds($mediainfo['General']['Duration']);
        }
        if (!empty($selected_item['size'])) {
            $item['mediainfo_file_size'] = bytesToGB($selected_item['size'], 2) . 'GB';
        }
        if (!empty($mediainfo)) {
            $item['mediainfo_tags'] = html_mediainfo_tags($mediainfo);
        }
    }

    $media_files_box_tpl = [
        'name' => 'media_files_box',
        'tpl_file' => 'media_selector',
        'tpl_pri' => 10,
        'tpl_place' => 'view',
        'tpl_place_var' => 'add_pre_actions',
        'tpl_vars' => $sel_values,
        'tpl_common_vars' => [
            'media_type' => $media_type,
            'selected_id' => $selected_id,
        ]
    ];
    $item['identify_btn'] = 1;
    if ($media_type == 'shows') {
        $item['identify_all_btn'] = 1;
    }

    $item['show_delete_opts'] = 1;

    if ($media_type == 'movies') {
        //localplayer
        if (!empty($cfg['localplayer'])) {
            $item['show_localplayer'] = 1;
        }
        //Download
        if (!empty($cfg['download_button'])) {
            $item['show_download_button'] = 1;
        }
    }

    return $media_files_box_tpl;
}
