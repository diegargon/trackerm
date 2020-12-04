<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function index_page() {
    global $cfg, $user, $LNG, $log;

    $titems = [];

    // General Info
    $tdata = [];
    $tdata['title'] = $LNG['L_IDENTIFIED'] . ': ' . strtoupper($user['username']);
    $tdata['content'] = $LNG['L_SEARCH_ENGINE'] . ': ' . '<a href="https://themoviedb.org" target=_blank>themoviedb.org</a>';
    $titems['col1'][] = getTpl('home-item', $tdata);

    // Profiles
    $tdata = [];
    $tdata['title'] = $LNG['L_PROFILES'];
    $tdata['content'] = '<div class="profiles">';

    $users = get_profiles();
    foreach ($users as $user) {
        $tdata['content'] .= '<a class="action_link" href="?userid=' . $user['id'] . '">' . strtoupper($user['username']) . '</a>';
    }

    $tdata['content'] .= '</div>';
    $titems['col1'][] = getTpl('home-item', $tdata);

    // Hard disk
    $tdata = [];
    $tdata['title'] = $LNG['L_HARDDISK'];

    $lib_stats = getLibraryStats();

    $tdata['content'] = "<span>{$LNG['L_MOVIES']} : " . $lib_stats['movies_size'] . '</span><br/>';
    $tdata['content'] .= "<span>{$LNG['L_SHOWS']} : " . $lib_stats['shows_size'] . '</span><br/>';
    $tdata['content'] .= "<span>{$LNG['L_FREE_TOTAL']} {$LNG['L_ON']} {$LNG['L_MOVIES']} : " . human_filesize(disk_free_space($cfg['MOVIES_PATH'])) . ' / ' . human_filesize(disk_total_space($cfg['MOVIES_PATH'])) . '</span><br/>';
    $tdata['content'] .= "<span>{$LNG['L_FREE_TOTAL']} {$LNG['L_ON']} {$LNG['L_SHOWS']} : " . human_filesize(disk_free_space($cfg['SHOWS_PATH'])) . ' / ' . human_filesize(disk_total_space($cfg['SHOWS_PATH'])) . '</span><br/>';
    $titems['col1'][] = getTpl('home-item', $tdata);

    // Database
    $tdata = [];
    $tdata['title'] = $LNG['L_DATABASE'];
    $tdata['content'] = "<span> {$LNG['L_MOVIES']} : {$lib_stats['num_movies']} </span><br/>";
    $tdata['content'] .= "<span> {$LNG['L_SHOWS']} : {$lib_stats['num_shows']} </span><br/>";
    $tdata['content'] .= "<span> {$LNG['L_EPISODES']} : {$lib_stats['num_episodes']} </span>";
    $titems['col1'][] = getTpl('home-item', $tdata);

    // States Messages
    isset($_POST['clear_state']) ? $log->clearStateMsgs() : null;
    $tdata = [];
    $tdata['title'] = $LNG['L_STATE_MSG'];
    $tdata['content'] = '<form method="POST"><input type="submit" class="submit_btn" name="clear_state" value="' . $LNG['L_CLEAR'] . '" />';
    $state_msgs = $log->getStateMsgs();

    if (!empty($state_msgs) && (count($state_msgs) > 0)) {
        foreach ($state_msgs as $state_msg) {
            $date = '[' . strftime("%d %h %X", strtotime($state_msg['created'])) . ']';
            $tdata['content'] .= '<div class="state_msg">' . $date . $state_msg['msg'] . '</div>';
        }
    }
    $tdata['main_class'] = 'home_state_msg';
    $titems['col2'][] = getTpl('home-item', $tdata);

    // Starting Info
    $tdata = [];
    $tdata['title'] = $LNG['L_STARTING'];
    $tdata['content'] = getfile('STARTING.' . substr($cfg['LANG'], 0, 2));
    $tdata['main_class'] = 'home_starting';
    $titems['col2'][] = getTpl('home-item', $tdata);

    // LATEST info
    $tdata = [];
    $tdata['title'] = $LNG['L_NEWS'];
    $tdata['content'] = '';
    $latest_ary = getfile_ary('LATEST');
    if (!empty($latest_ary)) {
        $latest_ary = array_slice($latest_ary, 2);
        foreach ($latest_ary as $latest) {
            $tdata['content'] .= $latest . '<br/>';
        }
    }
    $tdata['main_class'] = 'home_news';
    $titems['col2'][] = getTpl('home-item', $tdata);

    // LOGS
    isset($_POST['clear_log']) ? file_put_contents('cache/log/trackerm.log', '') : null;
    $tdata = [];
    $tdata['title'] = $LNG['L_LOGS'];
    $tdata['content'] = '<form method="POST"><input type="submit" class="submit_btn" name="clear_log" value="' . $LNG['L_CLEAR'] . '" />';
    $latest_ary = getfile_ary('cache/log/trackerm.log');
    if (!empty($latest_ary)) {
        foreach (array_reverse($latest_ary) as $latest) {
            if (!empty(trim($latest))) {
                $tdata['content'] .= $latest . '<br/>';
            }
        }
    }
    $tdata['main_class'] = 'home_log';
    $titems['col2'][] = getTpl('home-item', $tdata);

    //FIN
    $home = getTpl('home-page', $titems);

    return $home;
}

function page_view() {
    global $db, $LNG, $filter;

    $id = $filter->getInt('id');
    $deletereg = $filter->getInt('deletereg', 1);
    $type = $filter->getString('type');

    if (empty($id) || empty($type)) {
        return msg_box($msg = ['title' => $LNG['L_ERRORS'], 'body' => '1A1001']);
    }
    if (!empty($deletereg)) {
        if ($type == 'movies_library') {
            $db->deleteItemById('library_movies', $id);
        }
        if ($type == 'shows_library') {
            $delete_item = $db->getItemById('library_shows', $id);
            $media_db_id = $delete_item['themoviedb_id'];
            $db->deleteItemByField('library_shows', 'themoviedb_id', $media_db_id);
        }
        return msg_box($msg = ['title' => $LNG['L_SUCCESS'], 'body' => $LNG['L_DELETE_SUCCESSFUL']]);
    }

    return view();
}

function page_library() {
    global $LNG, $cfg;

    if (
            isset($_POST['num_id_show']) &&
            ($cfg['max_identify_items'] != $_POST['num_id_show'])
    ) {
        $cfg['max_identify_items'] = $_POST['num_id_show'];
        setPrefsItem('max_identify_items', $cfg['max_identify_items']);
    }
    ($cfg['max_identify_items'] == 0) ? $max_id_sel_0 = 'selected' : $max_id_sel_0 = '';
    ($cfg['max_identify_items'] == 5) ? $max_id_sel_5 = 'selected' : $max_id_sel_5 = '';
    ($cfg['max_identify_items'] == 10) ? $max_id_sel_10 = 'selected' : $max_id_sel_10 = '';
    ($cfg['max_identify_items'] == 20) ? $max_id_sel_20 = 'selected' : $max_id_sel_20 = '';
    ($cfg['max_identify_items'] == 50) ? $max_id_sel_50 = 'selected' : $max_id_sel_50 = '';

    $tdata['max_id_sel_0'] = $max_id_sel_0;
    $tdata['max_id_sel_5'] = $max_id_sel_5;
    $tdata['max_id_sel_10'] = $max_id_sel_10;
    $tdata['max_id_sel_20'] = $max_id_sel_20;
    $tdata['max_id_sel_50'] = $max_id_sel_50;

    /* ROWS */
    $max_rows_sel_none = '';

    if (isset($_POST['num_rows_results'])) {
        if ($_POST['num_rows_results'] == $LNG['L_DEFAULT']) {
            $max_rows_sel_none = 'selected';
        } else {
            $cfg['tresults_rows'] = $_POST['num_rows_results'];
            setPrefsItem('tresults_rows', $cfg['tresults_rows']);
        }
    }

    ($cfg['tresults_rows'] == 1) ? $tdata['max_rows_sel_1'] = 'selected' : $tdata['max_rows_sel_1'] = '';
    ($cfg['tresults_rows'] == 2) ? $tdata['max_rows_sel_2'] = 'selected' : $tdata['max_rows_sel_2'] = '';
    ($cfg['tresults_rows'] == 4) ? $tdata['max_rows_sel_4'] = 'selected' : $tdata['max_rows_sel_4'] = '';
    ($cfg['tresults_rows'] == 6) ? $tdata['max_rows_sel_6'] = 'selected' : $tdata['max_rows_sel_6'] = '';
    ($cfg['tresults_rows'] == 8) ? $tdata['max_rows_sel_8'] = 'selected' : $tdata['max_rows_sel_8'] = '';
    ($cfg['tresults_rows'] == 10) ? $tdata['max_rows_sel_10'] = 'selected' : $tdata['max_rows_sel_10'] = '';
    $tdata['max_rows_sel_none'] = $max_rows_sel_none;

    /* COLUMNS */

    $max_columns_sel_none = '';

    if (isset($_POST['num_columns_results'])) {
        if ($_POST['num_columns_results'] == $LNG['L_DEFAULT']) {
            $max_columns_sel_none = 'selected';
        } else {
            $cfg['tresults_columns'] = $_POST['num_columns_results'];
            setPrefsItem('tresults_columns', $cfg['tresults_columns']);
        }
    }

    ($cfg['tresults_columns'] == 1) ? $tdata['max_columns_sel_1'] = 'selected' : $tdata['max_columns_sel_1'] = '';
    ($cfg['tresults_columns'] == 2) ? $tdata['max_columns_sel_2'] = 'selected' : $tdata['max_columns_sel_2'] = '';
    ($cfg['tresults_columns'] == 4) ? $tdata['max_columns_sel_4'] = 'selected' : $tdata['max_columns_sel_4'] = '';
    ($cfg['tresults_columns'] == 6) ? $tdata['max_columns_sel_6'] = 'selected' : $tdata['max_columns_sel_6'] = '';
    ($cfg['tresults_columns'] == 8) ? $tdata['max_columns_sel_8'] = 'selected' : $tdata['max_columns_sel_8'] = '';
    ($cfg['tresults_columns'] == 10) ? $tdata['max_columns_sel_10'] = 'selected' : $tdata['max_columns_sel_10'] = '';
    $tdata['max_columns_sel_none'] = $max_columns_sel_none;
    /* FIN */

    $page = getTpl('library_options', array_merge($tdata, $LNG, $cfg));

    $page .= show_my_movies();
    $page .= show_my_shows();

    return $page;
}

function page_news() {
    global $cfg, $db, $log;

    $cache_movies_expire = 0;
    $cache_shows_expire = 0;

    if ($cfg['search_cache']) {

        $movies_cache_check = $db->getItemByField('jackett_search_movies_cache', 'words', '');
        !isset($movies_cache_check['update']) ? $movies_cache_check['update'] = 0 : null;

        if ((time() > ($movies_cache_check['update'] + $cfg['search_cache_expire']))) {
            $log->debug("News: Movies cache expire, Requesting");
            $cache_movies_expire = 1;
        } else {
            $log->debug("News:  Using movies cache");
            $ids = explode(',', $movies_cache_check['ids']);
            if (empty($ids) || count($ids) <= 0) {
                return false;
            }
            foreach ($ids as $cache_id) {
                $res_movies_db[] = $db->getItemById('jackett_movies', trim($cache_id));
            }
        }

        $shows_cache_check = $db->getItemByField('jackett_search_shows_cache', 'words', '');
        !isset($shows_cache_check['update']) ? $shows_cache_check['update'] = 0 : null;

        if ((time() > ($shows_cache_check['update'] + $cfg['search_cache_expire']))) {
            $log->debug("News: Shows cache expire, Requesting");
            $cache_shows_expire = 1;
        } else {
            $log->debug("News:  Using shows cache");
            $ids = explode(',', $shows_cache_check['ids']);
            if (empty($ids) || count($ids) <= 0) {
                return false;
            }
            foreach ($ids as $cache_id) {
                $res_shows_db[] = $db->getItemById('jackett_shows', trim($cache_id));
            }
        }
    }


    if (!$cfg['search_cache'] || $cache_movies_expire || $cache_shows_expire) {
        foreach ($cfg['jackett_indexers'] as $indexer) {
            $caps = jackett_get_caps($indexer);
            $categories = jackett_get_categories($caps['categories']['category']);

            $results = jackett_search_movies('', $indexer, $categories);
            ($results) ? $movies_res[$indexer] = $results : null;

            $results = null;
            $results = jackett_search_shows('', $indexer, $categories);
            $results ? $shows_res[$indexer] = $results : null;
        }
    }

    ($cache_movies_expire == 1) || !$cfg['search_cache'] ? $res_movies_db = jackett_prep_movies($movies_res) : null;
    ($cache_shows_expire == 1) || !$cfg['search_cache'] ? $res_shows_db = jackett_prep_shows($shows_res) : null;

    /* BUILD PAGE */

    $page_news = '';

    if (!empty($res_movies_db)) {
        $topt['search_type'] = 'movies';
        $page_news_movies = buildTable('L_MOVIES', $res_movies_db, $topt);
        $page_news .= $page_news_movies;
    }

    if (!empty($res_shows_db)) {
        $topt['search_type'] = 'shows';
        $page_news_shows = buildTable('L_SHOWS', $res_shows_db, $topt);
        $page_news .= $page_news_shows;
    }

    //UPDATE CACHE

    if (($cfg['search_cache'] && $cache_movies_expire)) {
        $movies_cache['words'] = '';
        $movies_cache['update'] = time();
        $movies_cache['ids'] = '';

        $last_element = end($res_movies_db);
        foreach ($res_movies_db as $tocache_movie) {
            $movies_cache['ids'] .= $tocache_movie['id'];
            $tocache_movie['id'] != $last_element['id'] ? $movies_cache['ids'] .= ', ' : null;
        }
        $db->upsertItemByField('jackett_search_movies_cache', $movies_cache, 'words');
    }
    if (($cfg['search_cache'] && $cache_shows_expire)) {
        $shows_cache['words'] = '';
        $shows_cache['update'] = time();
        $shows_cache['ids'] = '';

        $last_element = end($res_shows_db);
        foreach ($res_shows_db as $tocache_show) {
            $shows_cache['ids'] .= $tocache_show['id'];
            $tocache_show['id'] != $last_element['id'] ? $shows_cache['ids'] .= ', ' : null;
        }
        $db->upsertItemByField('jackett_search_shows_cache', $shows_cache, 'words');
    }


    return $page_news;
}

function page_tmdb() {
    global $LNG;

    (!empty($_GET['search_movies'])) ? $tdata['search_movies_word'] = $_GET['search_movies'] : $tdata['search_movies_word'] = '';
    (!empty($_GET['search_shows'])) ? $tdata['search_shows_word'] = $_GET['search_shows'] : $tdata['search_shows_word'] = '';

    $page = getTpl('page_tmdb', array_merge($LNG, $tdata));

    if (!empty($_GET['search_movies'])) {
        $movies = mediadb_searchMovies(trim($_GET['search_movies']));
        $topt['search_type'] = 'movies';
        !empty($movies) ? $page .= buildTable('L_DB', $movies, $topt) : null;
    }

    if (!empty($_GET['search_shows'])) {
        $shows = mediadb_searchShows(trim($_GET['search_shows']));
        $topt['search_type'] = 'shows';
        !empty($shows) ? $page .= buildTable('L_DB', $shows, $topt) : null;
    }

    return $page;
}

function page_torrents() {
    global $LNG;

    (!empty($_GET['search_movies_torrents'])) ? $tdata['search_movies_word'] = $_GET['search_movies_torrents'] : $tdata['search_movies_word'] = '';
    (!empty($_GET['search_shows_torrents'])) ? $tdata['search_shows_word'] = $_GET['search_shows_torrents'] : $tdata['search_shows_word'] = '';

    $page = getTpl('page_torrents', array_merge($tdata, $LNG));

    if (!empty($_GET['search_movies_torrents'])) {
        $page .= search_movies_torrents(trim($_GET['search_movies_torrents']), 'L_TORRENT');
    }

    if (!empty($_GET['search_shows_torrents'])) {
        $torrent_results = search_shows_torrents(trim($_GET['search_shows_torrents']), 'L_TORRENT');

        if ($torrent_results !== false) {
            $page .= $torrent_results;
        } else {
            $box_msg['title'] = $LNG['L_ERROR'] . ':' . $LNG['L_TORRENT'];
            $box_msg['body'] = $LNG['L_NOTHING_FOUND'];
            $page .= msg_box($box_msg);
        }
    }

    return $page;
}

function page_wanted() {
    global $LNG, $cfg, $db, $filter;

    $want = [];

    if (isset($_POST['check_day'])) {
        $wanted_mfy = $filter->postInt('check_day');
        foreach ($wanted_mfy as $w_mfy_id => $w_mfy_value) {
            $day_check['day_check'] = $w_mfy_value;
            $db->updateItemById('wanted', $w_mfy_id, $day_check);
        }
    }

    isset($_GET['id']) ? $wanted_id = $_GET['id'] : $wanted_id = false;
    isset($_GET['media_type']) ? $wanted_type = $_GET['media_type'] : $wanted_type = false;
    isset($_GET['delete']) && $filter->getInt('delete') ? $db->deleteItemById('wanted', $filter->getInt('delete')) : null;

    if (isset($_GET['ignore'])) {
        $ignore_id = $_GET['ignore'];
        $wanted_ignore_item = $db->getItemById('wanted', $ignore_id);
        if (empty($wanted_ignore_item['ignore'])) {
            $update['ignore'] = 1;
        } else {
            $update['ignore'] = 0;
        }
        $db->updateItemById('wanted', $ignore_id, $update);
    }

    if ($wanted_id !== false && $wanted_type !== false && $wanted_type == 'movies') {
        wanted_movies($wanted_id);
    }

    $want['wanted_list'] = wanted_list();

    return getTpl('wanted', array_merge($want, $LNG, $cfg));
}

function page_identify() {
    global $LNG, $db, $filter;

    $media_type = $filter->getString('media_type');
    $id = $filter->getInt('identify');

    if ($media_type === false || $id === false) {
        $box_msg['title'] = $LNG['L_ERROR'];
        $box_msg['body'] = '1A1002';
        return msg_box($box_msg);
    }

    $tdata['head'] = '';

    if ($media_type == 'movies') {
        $item = $db->getItemById('library_movies', $id);
    } else {
        $item = $db->getItemById('library_shows', $id);
    }

    if (isset($_POST['identify']) && isset($_POST['selected'])) {

        submit_ident($media_type, $_POST['selected']);

        return msg_box($msg = ['title' => $LNG['L_SUCCESS'], 'body' => $LNG['L_ADDED_SUCCESSFUL']]);
    }
    !empty($_POST['submit_title']) ? $tdata['search_title'] = $_POST['submit_title'] : $tdata['search_title'] = $item['predictible_title'];

    $item_selected = [];

    if (!empty($_POST['submit_title'])) {
        if ($media_type == 'movies') {
            $db_media = mediadb_searchMovies($_POST['submit_title']);
        } else {
            $db_media = mediadb_searchShows($_POST['submit_title']);
        }
        $results_opt = '';
        if (!empty($db_media)) {
            $select = '';

            foreach ($db_media as $db_item) {
                //var_dump($db_item);
                if (!empty($_POST['selected']) && ($db_item['id'] == current($_POST['selected']))) {
                    $selected = 'selected';
                    $item_selected = $db_item;
                } else {
                    $selected = '';
                }
                $year = trim(substr($db_item['release'], 0, 4));
                $results_opt .= '<option ' . $selected . ' value="' . $db_item['id'] . '">';
                $results_opt .= $db_item['title'];
                !empty($year) ? $results_opt .= ' (' . $year . ')' : null;
                $results_opt .= '</option>';
            }

            if ($media_type == 'movies') {
                $select .= '<select onchange="this.form.submit()" class="ident_select" name="selected[' . $id . ']">' . $results_opt . '</select>';
            } else if ($media_type == 'shows') {
                $select .= '<select onchange="this.form.submit()" class="ident_select" name="selected[' . $id . ']">' . $results_opt . '</select>';
            }
            $tdata['select'] = $select;
        }
    }

    if (count($item_selected) > 1) {
        isset($item_selected['poster']) ? $tdata['selected_poster'] = $item_selected['poster'] : null;
        isset($item_selected['plot']) ? $tdata['selected_plot'] = $item_selected['plot'] : null;
    } else {
        if (isset($db_media) && count($db_media) > 1) {
            $first_item = current($db_media);
            isset($first_item['poster']) ? $tdata['selected_poster'] = $first_item['poster'] : null;
            isset($first_item['plot']) ? $tdata['selected_plot'] = $first_item['plot'] : null;
        }
    }
    return getTpl('identify_adv', array_merge($LNG, $item, $tdata));
}

function page_download() {
    global $db, $filter;

    $id = $filter->getInt('id');
    $type = $filter->getString('type');

    if (empty($id) || empty($type)) {
        exit();
    }

    if ($type == 'movies_library') {
        $item = $db->getItemById('library_movies', $id);
    } else if ($type == 'shows_library') {
        $item = $db->getItemById('library_shows', $id);
    } else {
        exit();
    }
    if (!empty($item) && file_exists($item['path'])) {
        send_file($item['path']);
    }
    exit();
}
