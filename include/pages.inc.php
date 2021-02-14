<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function page_index() {
    global $cfg, $user, $LNG, $log, $filter;

    $titems = [];
    $status_msg = '';

    // Config
    if (!empty($user['isAdmin'])) {
        $tdata = [];
        $tdata['title'] = '';
        $tdata['content'] = '<a class="action_link" href="index.php?page=config">' . $LNG['L_CONFIG'] . '</a>';
        $titems['col1'][] = getTpl('home-item', $tdata);
    }
    // General Info
    $tdata = [];
    $tdata['content'] = '';
    $tdata['title'] = $LNG['L_IDENTIFIED'] . ': ' . strtoupper($user['username']);

    if ($filter->getInt('edit_profile')) {
        $tdata['content'] .= '<form method="POST" id="form_user_prefs" action="?page=index&edit_profile=1">';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            (isset($_POST['cur_password']) && isset($_POST['new_password'])) ? $status_msg .= user_change_password() . '<br/>' : null;
            $status_msg .= user_change_prefs();
        }
        $tdata['content'] .= user_edit_profile();
        $tdata['content'] .= '<input type="submit" class="action_link inline" value="' . $LNG['L_SEND'] . '"/>';
        $tdata['content'] .= '</form>';
    } else {
        $tdata['content'] .= '<a class="action_link" href="?page=index&edit_profile=1">' . $LNG['L_EDIT'] . '</a>';
    }

    $tdata['content'] .= '<a class="action_link" href="?page=logout">' . $LNG['L_LOGOUT'] . '</a>';
    $tdata['content'] .= '<br/>';
    empty($status_msg) ? $status_msg = $LNG['L_SEARCH_ENGINE'] . ': ' . '<a href="https://themoviedb.org" target=_blank>themoviedb.org</a>' : null;
    $tdata['content'] .= $status_msg;
    $titems['col1'][] = getTpl('home-item', $tdata);

    // User managament
    if (!empty($user['isAdmin'])) {
        $tdata = [];
        $tdata = user_management();
        $titems['col1'][] = getTpl('home-item', $tdata);
    }
    // Hard disk
    $tdata = [];
    $tdata['title'] = '';

    $lib_stats = getLibraryStats();

    $tdata['content'] = "<h3>{$LNG['L_LIBRARY']}</h3>";
    $tdata['content'] .= "<span> {$LNG['L_MOVIES']} : {$lib_stats['num_movies']} </span><br/>";
    $tdata['content'] .= "<span> {$LNG['L_SHOWS']} : {$lib_stats['num_shows']} </span>";
    $tdata['content'] .= "<span> ({$LNG['L_EPISODES']} : {$lib_stats['num_episodes']}) </span><br/>";
    $tdata['content'] .= "<h3>{$LNG['L_HARDDISK']}</h3>";
    $tdata['content'] .= "<span>{$LNG['L_MOVIES']} : " . $lib_stats['movies_size'] . '</span><br/>';
    $tdata['content'] .= "<span>{$LNG['L_SHOWS']} : " . $lib_stats['shows_size'] . '</span><br/>';

    if (is_array($cfg['MOVIES_PATH'])) {
        foreach ($cfg['MOVIES_PATH'] as $movies_path) {
            $movies_path_name = basename($movies_path);
            $movies_free_space = human_filesize(disk_free_space($movies_path));
            $movies_total_space = human_filesize(disk_total_space($movies_path));
            $tdata['content'] .= "<span>{$LNG['L_FREE_TOTAL']} {$LNG['L_ON']} {$movies_path_name} : {$movies_free_space} / {$movies_total_space} </span><br/>";
        }
    } else {
        $tdata['content'] .= "<span>{$LNG['L_FREE_TOTAL']} {$LNG['L_ON']} {$LNG['L_MOVIES']} : " . human_filesize(disk_free_space($cfg['MOVIES_PATH'])) . ' / ' . human_filesize(disk_total_space($cfg['MOVIES_PATH'])) . '</span><br/>';
    }
    if (is_array($cfg['SHOWS_PATH'])) {
        foreach ($cfg['SHOWS_PATH'] as $shows_path) {
            $shows_path_name = basename($shows_path);
            $shows_free_space = human_filesize(disk_free_space($shows_path));
            $shows_total_space = human_filesize(disk_total_space($shows_path));
            $tdata['content'] .= "<span>{$LNG['L_FREE_TOTAL']} {$LNG['L_ON']} {$shows_path_name} : {$shows_free_space} / {$shows_total_space} </span><br/>";
        }
    } else {
        $tdata['content'] .= "<span>{$LNG['L_FREE_TOTAL']} {$LNG['L_ON']} {$LNG['L_SHOWS']} : " . human_filesize(disk_free_space($cfg['SHOWS_PATH'])) . ' / ' . human_filesize(disk_total_space($cfg['SHOWS_PATH'])) . '</span><br/>';
    }

    $tdata['content'] .= "<h3>{$LNG['L_DATABASE']}</h3>";
    $tdata['content'] .= "<span> {$LNG['L_SIZE']} : {$lib_stats['db_size']} </span><br/>";

    $titems['col1'][] = getTpl('home-item', $tdata);

    // States Messages
    isset($_POST['clear_state']) ? $log->clearStateMsgs() : null;
    $tdata = [];
    $tdata['title'] = $LNG['L_STATE_MSG'];
    $tdata['content'] = '<form method="POST"><input type="submit" class="submit_btn clear_btn" name="clear_state" value="' . $LNG['L_CLEAR'] . '" /></form>';
    $state_msgs = $log->getStateMsgs();

    if (!empty($state_msgs) && (count($state_msgs) > 0)) {
        foreach ($state_msgs as $state_msg) {
            $tdata['content'] .= '<div class="state_msg_block">';
            $tdata['content'] .= '<div class="state_time">[' . strftime("%d %h %X", strtotime($state_msg['created'])) . ']</div>';
            $tdata['content'] .= '<div class="state_msg">' . $state_msg['msg'] . '</div>';
            $tdata['content'] .= '</div>';
        }
    }
    $tdata['main_class'] = 'home_state_msg';
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
    $tdata['content'] = '<form method="POST"><input type="submit" class="submit_btn clear_btn" name="clear_log" value="' . $LNG['L_CLEAR'] . '" /></form>';
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

    // Starting Info
    $tdata = [];
    $tdata['title'] = $LNG['L_STARTING'];
    $tdata['content'] = getfile('STARTING.' . substr($cfg['LANG'], 0, 2));
    $tdata['main_class'] = 'home_starting';
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
        return msg_box($msg = ['title' => $LNG['L_ERROR'], 'body' => '1A1001']);
    }
    if (!empty($deletereg)) {
        if ($type == 'movies_library') {
            $db->deleteItemById('library_movies', $id);
        }
        if ($type == 'shows_library') {
            $delete_item = $db->getItemById('library_shows', $id);
            $media_db_id = $delete_item['themoviedb_id'];
            $db->deleteItemsByField('library_shows', 'themoviedb_id', $media_db_id);
        }
        return msg_box($msg = ['title' => $LNG['L_SUCCESS'], 'body' => $LNG['L_DELETE_SUCCESSFUL']]);
    }

    return view();
}

function page_library() {
    global $cfg;

    $page = '';

    if (($cfg['want_movies']) && ( $_GET['page'] == 'library' || $_GET['page'] == 'library_movies')) {
        $page .= show_my_movies();
    }
    if (($cfg['want_shows']) && ($_GET['page'] == 'library' || $_GET['page'] == 'library_shows')) {
        $page .= show_my_shows();
    }

    return $page;
}

function page_news() {
    global $cfg;

    $page_news = '';
    if (($cfg['want_movies']) && ($_GET['page'] == 'news' || $_GET['page'] == 'new_movies')) {
        $page_news .= page_new_media('movies');
    }
    if (($cfg['want_shows']) && ($_GET['page'] == 'news' || $_GET['page'] == 'new_shows')) {
        $page_news .= page_new_media('shows');
    }
    return $page_news;
}

function page_tmdb() {
    global $LNG, $filter, $cfg;

    (!empty($_GET['search_movies'])) ? $search_movies = $filter->getUtf8('search_movies') : $search_movies = '';
    (!empty($_GET['search_shows'])) ? $search_shows = $filter->getUtf8('search_shows') : $search_shows = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['show_trending'])) {
            $show_trending = $filter->postInt('show_trending');
            if (!empty($show_trending)) {
                setPrefsItem('show_trending', 1);
            } else {
                setPrefsItem('show_trending', 0);
            }
        }

        if (isset($_POST['show_popular'])) {
            $show_popular = $filter->postInt('show_popular');
            if (!empty($show_popular)) {
                setPrefsItem('show_popular', 1);
            } else {
                setPrefsItem('show_popular', 0);
            }
        }
        if (isset($_POST['show_today_shows'])) {
            $show_today_shows = $filter->postInt('show_today_shows');
            if (!empty($show_today_shows)) {
                setPrefsItem('show_today_shows', 1);
            } else {
                setPrefsItem('show_today_shows', 0);
            }
        }
    }
    !empty(getPrefsItem('show_trending')) ? $tdata['TRENDING_CHECKED'] = 'checked' : $tdata['TRENDING_CHECKED'] = '';
    !empty(getPrefsItem('show_popular')) ? $tdata['POPULAR_CHECKED'] = 'checked' : $tdata['POPULAR_CHECKED'] = '';
    !empty(getPrefsItem('show_today_shows')) ? $tdata['TODAYSHOWS_CHECKED'] = 'checked' : $tdata['TODAYSHOWS_CHECKED'] = '';
    $tdata['search_movies_word'] = $search_movies;
    $tdata['search_shows_word'] = $search_shows;

    $page = getTpl('page_tmdb', array_merge($LNG, $tdata, $cfg));

    if (!empty($search_movies)) {
        $movies = mediadb_searchMovies(trim($search_movies));
        $topt['search_type'] = 'movies';
        !empty($movies) ? $page .= buildTable('L_DB', $movies, $topt) : null;
    }

    if (!empty($search_shows)) {
        $shows = mediadb_searchShows(trim($search_shows));
        $topt['search_type'] = 'shows';
        !empty($shows) ? $page .= buildTable('L_DB', $shows, $topt) : null;
    }
    if (!isset($_GET['search_movies']) && !isset($_GET['search_shows']) && !empty(getPrefsItem('show_trending'))) {
        $topt['no_pages'] = 1;
        $results = mediadb_getTrending();
        ($cfg['want_movies']) ? $page .= buildTable('L_TRENDING_MOVIES', $results['movies'], $topt) : null;
        ($cfg['want_shows']) ? $page .= buildTable('L_TRENDING_SHOWS', $results['shows'], $topt) : null;
    }

    if (!isset($_GET['search_movies']) && !isset($_GET['search_shows']) && !empty(getPrefsItem('show_popular'))) {
        $topt['no_pages'] = 1;
        $results = mediadb_getPopular();
        ($cfg['want_movies']) ? $page .= buildTable('L_POPULAR_MOVIES', $results['movies'], $topt) : null;
        ($cfg['want_shows']) ? $page .= buildTable('L_POPULAR_SHOWS', $results['shows'], $topt) : null;
    }

    if (!isset($_GET['search_movies']) && !isset($_GET['search_shows']) && !empty(getPrefsItem('show_today_shows'))) {
        $topt['no_pages'] = 1;
        $results = mediadb_getTodayShows();
        $page .= buildTable('L_TODAY_SHOWS', $results['shows'], $topt);
    }
    return $page;
}

function page_torrents() {
    global $LNG, $filter, $cfg;

    (!empty($_GET['search_movies_torrents'])) ? $search_movies_torrents = $filter->getUtf8('search_movies_torrents') : $search_movies_torrents = '';
    (!empty($_GET['search_shows_torrents'])) ? $search_shows_torrents = $filter->getUtf8('search_shows_torrents') : $search_shows_torrents = '';

    $tdata['search_movies_word'] = $search_movies_torrents;
    $tdata['search_shows_word'] = $search_shows_torrents;

    $page = getTpl('page_torrents', array_merge($tdata, $LNG, $cfg));

    if (!empty($search_movies_torrents)) {
        $search['words'] = trim($search_movies_torrents);
        $page .= search_media_torrents('movies', $search, 'L_TORRENT');
    }

    if (!empty($search_shows_torrents)) {
        $search['words'] = trim($search_shows_torrents);
        $torrent_results = search_media_torrents('shows', $search, 'L_TORRENT');

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
    global $LNG, $cfg, $db, $filter, $trans;

    $want = [];
    $trans->updateWanted();

    if (isset($_POST['check_day'])) {
        $wanted_mfy = $filter->postInt('check_day');
        foreach ($wanted_mfy as $w_mfy_id => $w_mfy_value) {
            $day_check['day_check'] = $w_mfy_value;
            $db->updateItemById('wanted', $w_mfy_id, $day_check);
        }
    }

    isset($_GET['id']) ? $wanted_id = $filter->getInt('id') : $wanted_id = false;
    isset($_GET['media_type']) ? $wanted_type = $filter->getString('media_type') : $wanted_type = false;
    isset($_GET['delete']) && $filter->getInt('delete') ? $db->deleteItemById('wanted', $filter->getInt('delete')) : null;

    if ($wanted_id !== false && $wanted_type !== false && $wanted_type == 'movies') {
        wanted_movies($wanted_id);
    }

    $want['wanted_list'] = wanted_list();

    return !empty($want['wanted_list']) ? getTpl('wanted', array_merge($want, $LNG, $cfg)) : false;
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

    if (isset($_POST['identify']) && $filter->postInt('selected')) {
        ident_by_idpairs($media_type, $filter->postInt('selected'));
        return msg_box($msg = ['title' => $LNG['L_SUCCESS'], 'body' => $LNG['L_ADDED_SUCCESSFUL']]);
    }
    !empty($_POST['submit_title']) ? $submit_title = $filter->postUtf8('submit_title') : $submit_title = $item['predictible_title'];

    $tdata['search_title'] = $submit_title;

    $item_selected = [];

    if (!empty($submit_title)) {
        ($media_type == 'movies') ? $db_media = mediadb_searchMovies($submit_title) : $db_media = mediadb_searchShows($submit_title);

        $results_opt = '';
        if (!empty($db_media)) {
            $select = '';

            foreach ($db_media as $db_item) {
                //var_dump($db_item);
                if (!empty($filter->postInt('selected')) && ($db_item['themoviedb_id'] == current($filter->postInt('selected')))) {
                    $selected = 'selected';
                    $item_selected = $db_item;
                } else {
                    $selected = '';
                }
                $year = trim(substr($db_item['release'], 0, 4));
                $results_opt .= '<option ' . $selected . ' value="' . $db_item['themoviedb_id'] . '">';
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
    (!empty($item) && file_exists($item['path'])) ? send_file($item['path']) : null;

    exit();
}

function page_transmission() {
    global $trans, $LNG, $filter;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $tid = $filter->postInt('tid');

        isset($_POST['start_all']) ? $trans->startAll() . sleep(1) : null;
        isset($_POST['stop_all']) ? $trans->stopAll() . sleep(1) : null;

        if (!empty($tid)) {
            isset($_POST['start']) ? $trans->start($tid) . usleep(500000) : null;
            isset($_POST['stop']) ? $trans->stop($tid) . usleep(500000) : null;
            isset($_POST['delete']) ? $trans->delete($tid) . usleep(500000) : null;
        }
    }
    $transfers = $trans->getAll();

    $page = '';
    $tdata['body'] = '';

    foreach ($transfers as $transfer) {
        $transfer['status'] == 0 ? $tdata['show_start'] = 1 : $tdata['show_start'] = 0;
        $transfer['status'] != 0 && $transfer['status'] < 8 ? $tdata['show_stop'] = 1 : $tdata['show_stop'] = 0;

        $tdata['status_name'] = $trans->getStatusName($transfer['status']);
        $transfer['percentDone'] == 1 ? $tdata['percent'] = '100' : $tdata['percent'] = ((float) $transfer['percentDone']) * 100;
        $tdata['body'] .= getTpl('transmission-row', array_merge($transfer, $tdata, $LNG));
    }

    $page .= getTpl('transmission-body', array_merge($tdata, $LNG));

    return $page;
}

function page_config() {
    global $filter, $config;

    $page = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['submit_config'])) {

            $config_keys = $filter->postString('config_keys');
            if (!empty($config_keys) && is_array($config_keys) && count($config_keys) > 0) {
                $config->saveKeys($config_keys);
            }
        }
        //FIXME: This way for handle is messy
        if (isset($_POST['config_remove']) && isset($_POST['config_id'][array_key_first($_POST['config_remove'])])) {
            $key = array_key_first($_POST['config_remove']);
            $id = $_POST['config_id'][array_key_first($_POST['config_remove'])];
            $config->removeCommaElement($key, $id);
        }
        if (isset($_POST['config_add']) && !empty($_POST['add_item'][array_key_first($_POST['config_add'])])) {
            $key = array_key_first($_POST['config_add']);
            $value = $_POST['add_item'][array_key_first($_POST['config_add'])];
            $value = $filter->varString($value);
            if (isset($_POST['config_id'][array_key_first($_POST['config_add'])])) {
                $id = $_POST['config_id'][array_key_first($_POST['config_add'])];
            } else {
                $id = null;
            }
            empty($_POST['add_before'][array_key_first($_POST['config_add'])]) ? $before = 0 : $before = 1;
            $config->addCommaElement($key, trim($value), $id, $before);
        }
    }
    $page .= $config->display($filter->getString('category'));

    return $page;
}

function page_login() {
    global $cfg, $db, $filter;


    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $dologin = 0;

        $username = $filter->postUsername('username');
        $password = $filter->postUsername('password');
        if (!empty($username)) {
            if ($cfg['force_use_passwords'] && !empty($password)) {
                $dologin = 1;
            } else if (!$cfg['force_use_passwords']) {
                $dologin = 1;
            }
            if ($dologin) {
                $userid = check_user($username, $password);
                if ($userid) {
                    set_user($userid);
                    header("Location: {$cfg['REL_PATH']} ");
                    exit();
                }
            }
        }
    }
    $tdata = [];
    $result = $db->select('users');
    $users = $db->fetchAll($result);
    $page = '';
    $tdata['profiles'] = '';
    foreach ($users as $user) {
        if ($user['disable'] != 1 && $user['hide_login'] != 1) {
            $tdata['profiles'] .= getTpl('profile_box', array_merge($tdata, $cfg, $user));
        }
    }
    $page .= getTpl('login', array_merge($tdata));
    return $page;
}

function page_logout() {
    global $cfg;

    $_SESSION['uid'] = 0;
    ($_COOKIE) ? setcookie("uid", null, -1) : null;
    session_regenerate_id();
    session_destroy();
    header("Location: {$cfg['REL_PATH']} ");
    exit(0);
}

function page_localplayer() {
    global $cfg, $filter, $db;

    $id = $filter->getInt('id');
    $media_type = $filter->getString('media_type');

    if (empty($id) || empty($media_type)) {
        exit();
    }
    if ($media_type == 'movies') {
        $item = $db->getItemById('library_movies', $id);
    } else {

        $item = $db->getItemById('library_shows', $id);
    }

    if (empty($item)) {
        exit();
    }

    $path = str_replace($cfg['playlocal_root_path'], '', $item['path']);

    header("Content-Type: video/mpegurl");
    header("Content-Disposition: attachment; filename={$item['title']}.m3u8");
    header("Pragma: no-cache");
    header("Expires: 0");
    print "#EXTM3U\r\n";
    print "#EXTINF:123, {$item['title']}\r\n";
    if (isMobile() || isLinux()) {
        print $cfg['playlocal_share_linux_path'] . $path . "\r\n";
    } else {
        print $cfg['playlocal_share_windows_path'] . $path . "\r\n";
    }
    print "#EXT-X-ENDLIST";
    exit(0);
}
