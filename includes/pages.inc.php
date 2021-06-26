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
    global $cfg, $db, $user, $LNG, $log, $frontend;

    $titems = [];
    $status_msg = '';

    // Config
    if (!empty($user['isAdmin'])) {
        $tdata = [];
        $tdata['title'] = '';
        $tdata['content'] = Html::form(['id' => 'clear_disabled', 'method' => 'post'], Html::input(['type' => 'submit', 'name' => 'clear_disabled', 'value' => $LNG['L_CLEAR_DISABLE']]));
        $tdata['content'] .= Html::form(['id' => 'clear_search_cache', 'method' => 'post'], Html::input(['type' => 'submit', 'name' => 'clear_search_cache', 'value' => $LNG['L_CLEAR_SEARCH_CACHE']]));
        $tdata['content'] .= Html::link(['class' => 'action_link'], 'index.php', $LNG['L_CONFIG'], ['page' => 'config']);

        $titems['col1'][] = $frontend->getTpl('home-item', $tdata);
    }
    // General Info
    $tdata = [];
    $tdata['content'] = '';
    $tdata['title'] = $LNG['L_IDENTIFIED'] . ': ' . strtoupper($user['username']);

    if (Filter::getInt('edit_profile')) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            (isset($_POST['cur_password']) && isset($_POST['new_password'])) ? $status_msg .= user_change_password() . '<br/>' : null;
            $status_msg .= user_change_prefs();
        }
        $tdata['content'] .= user_edit_profile();
    } else {
        $tdata['content'] = Html::link(['class' => 'action_link'], '', $LNG['L_EDIT'], ['page' => 'index', 'edit_profile' => 1]);
    }

    if (isset($_POST['clear_disabled'])) {
        foreach ($cfg['jackett_indexers'] as $indexer) {
            $indexer_disabled = $indexer . '_disable';
            $db->delete('preferences', ['pref_name' => ['value' => $indexer_disabled]], 'LIMIT 1');
        }
    }
    if (isset($_POST['clear_search_cache'])) {
        $db->delete('jackett_search_movies_cache');
        $db->delete('jackett_search_shows_cache');
        $db->delete('search_movies_cache');
        $db->delete('search_shows_cache');
    }
    $tdata['content'] .= Html::link(['class' => 'action_link'], '', $LNG['L_LOGOUT'], ['page' => 'logout']);
    $tdata['content'] .= $status_msg;
    $titems['col1'][] = $frontend->getTpl('home-item', $tdata);

    // User managament
    if (!empty($user['isAdmin'])) {
        $tdata = [];
        $tdata = user_management();
        $titems['col1'][] = $frontend->getTpl('home-item', $tdata);
    }
    // Hard disk
    $tdata = [];
    $tdata['title'] = '';
    $lib_stats = getLibraryStats();
    $paths['movies_paths'] = '';
    $paths['shows_paths'] = '';

    if (isset($lib_stats['movies_paths']) && valid_array($lib_stats['movies_paths'])) {
        foreach ($lib_stats['movies_paths'] as $path) {
            $paths['movies_paths'] = Html::span(['class' => 'harddisk_paths'], "{$LNG['L_FREE_TOTAL']} {$LNG['L_ON']} {$path['basename']} : {$path['free']} / {$path['total']}");
        }
    }
    if (isset($lib_stats['shows_paths']) && valid_array($lib_stats['shows_paths'])) {
        foreach ($lib_stats['shows_paths'] as $path) {
            $paths['shows_paths'] = Html::span(['class' => 'harddisk_paths'], "{$LNG['L_FREE_TOTAL']} {$LNG['L_ON']} {$path['basename']} : {$path['free']} / {$path['total']}");
        }
    }
    $tdata['content'] = $frontend->getTpl('harddisk', array_merge($lib_stats, $paths));
    $titems['col1'][] = $frontend->getTpl('home-item', $tdata);

    // States Messages
    isset($_POST['clear_state']) ? $log->clearStateMsgs() : null;
    $tdata = [];
    $tdata['title'] = $LNG['L_STATE_MSG'];
    $clean_link = Html::input(['type' => 'submit', 'class' => 'submit_btn clear_btn', 'name' => 'clear_state', 'value' => $LNG['L_CLEAR']]);
    $tdata['content'] = Html::form(['method' => 'POST'], $clean_link);
    $state_msgs = $log->getStateMsgs();

    if (!empty($state_msgs) && (count($state_msgs) > 0)) {
        foreach ($state_msgs as $state_msg) {
            $state_msg['display_time'] = strftime("%d %h %X", strtotime($state_msg['created']));
            $tdata['content'] .= $frontend->getTpl('statemsg_item', $state_msg);
        }
    }
    $tdata['main_class'] = 'home_state_msg';
    $titems['col2'][] = $frontend->getTpl('home-item', $tdata);

    // LATEST info
    $tdata = [];
    $tdata['title'] = $LNG['L_NEWS'];
    $tdata['content'] = '';
    $latest_ary = getfile_ary('LATEST');
    if (!empty($latest_ary)) {
        $latest_ary = array_slice($latest_ary, 2);
        foreach ($latest_ary as $latest) {
            $tdata['content'] .= Html::div(['class' => 'divBlock'], $latest);
        }
    }
    $tdata['main_class'] = 'home_news';
    $titems['col2'][] = $frontend->getTpl('home-item', $tdata);

    // LOGS
    isset($_POST['clear_log']) ? file_put_contents('cache/log/trackerm.log', '') : null;
    $tdata = [];
    $tdata['title'] = $LNG['L_LOGS'];
    $clean_link = Html::input(['type' => 'submit', 'class' => 'submit_btn clear_btn', 'name' => 'clear_log', 'value' => $LNG['L_CLEAR']]);
    $tdata['content'] = Html::form(['method' => 'POST'], $clean_link);
    $latest_ary = getfile_log('cache/log/trackerm.log');
    if (!empty($latest_ary)) {
        foreach (array_reverse($latest_ary) as $latest) {
            if (!empty(trim($latest))) {
                $tdata['content'] .= Html::div(['class' => 'divBlock'], $latest);
            }
        }
    }
    $tdata['main_class'] = 'home_log';
    $titems['col2'][] = $frontend->getTpl('home-item', $tdata);

    // Starting Info
    $tdata = [];
    $tdata['title'] = $LNG['L_STARTING'];
    $tdata['content'] = getfile('STARTING.' . substr($cfg['LANG'], 0, 2));
    $tdata['main_class'] = 'home_starting';
    $titems['col2'][] = $frontend->getTpl('home-item', $tdata);

    //FIN
    $home = $frontend->getTpl('home-page', $titems);

    return $home;
}

function page_view() {
    global $db, $frontend;

    $id = Filter::getInt('id');
    $deletereg_id = Filter::getInt('deletereg_id');
    $view_type = Filter::getString('view_type');

    if (empty($id) || empty($view_type)) {
        return $frontend->msgBox($msg = ['title' => 'L_ERROR', 'body' => '1A1001']);
    }
    if (!empty($deletereg_id)) {
        if ($view_type == 'movies_library') {
            $library = 'library_movies';
            $library_master = 'library_master_movies';
        } else if ($view_type == 'shows_library') {
            $library = 'library_shows';
            $library_master = 'library_master_shows';
        }
        $register_item = $db->getItemById($library, $deletereg_id);
        if (valid_array($register_item)) {
            $register_master_item = $db->getItemById($library_master, $register_item['master']);
            if (valid_array($register_master_item)) {
                if ($register_master_item['total_items'] == 1) {
                    $db->deleteItemById($library, $deletereg_id);
                    $db->deleteItemById($library_master, $register_item['master']);
                    return $frontend->msgBox($msg = ['title' => 'L_SUCCESS', 'body' => 'L_DELETE_SUCCESSFUL']);
                } else {
                    $db->deleteItemById($library, $deletereg_id);
                    $new_total_items = $register_master_item['total_items'] - 1;
                    $new_total_size = $register_master_item['total_size'] - $register_item['size'];
                    $db->updateItemById($library_master, $register_item['master'], ['total_items' => $new_total_items, 'total_size' => $new_total_size]);
                }
            }
        }
    }

    return view();
}

function page_library() {
    global $cfg;

    $page = '';

    (isset($_POST['rebuild_movies'])) ? rebuild('movies', $cfg['MOVIES_PATH']) : null;
    (isset($_POST['rebuild_shows'])) ? rebuild('shows', $cfg['SHOWS_PATH']) : null;

    if (($cfg['want_movies']) && ( $_GET['page'] == 'library' || $_GET['page'] == 'library_movies')) {
        $page .= show_my_media('movies');
    }
    if (($cfg['want_shows']) && ($_GET['page'] == 'library' || $_GET['page'] == 'library_shows')) {
        $page .= show_my_media('shows');
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
    global $cfg, $frontend;

    (!empty($_GET['search_movies'])) ? $search_movies = Filter::getUtf8('search_movies') : $search_movies = '';
    (!empty($_GET['search_shows'])) ? $search_shows = Filter::getUtf8('search_shows') : $search_shows = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['show_trending'])) {
            $show_trending = Filter::postInt('show_trending');
            if (!empty($show_trending)) {
                setPrefsItem('show_trending', 1);
            } else {
                setPrefsItem('show_trending', 0);
            }
        }

        if (isset($_POST['show_popular'])) {
            $show_popular = Filter::postInt('show_popular');
            if (!empty($show_popular)) {
                setPrefsItem('show_popular', 1);
            } else {
                setPrefsItem('show_popular', 0);
            }
        }
        if (isset($_POST['show_today_shows'])) {
            $show_today_shows = Filter::postInt('show_today_shows');
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

    $page = $frontend->getTpl('page_tmdb', $tdata);

    if (!empty($search_movies)) {
        $movies = mediadb_searchMovies(trim($search_movies));
        $topt['search_type'] = 'movies';
        $topt['view_type'] = 'movies_db';
        !empty($movies) ? $page .= buildTable('L_DB', $movies, $topt) : null;
    }

    if (!empty($search_shows)) {
        $shows = mediadb_searchShows(trim($search_shows));
        $topt['search_type'] = 'shows';
        $topt['view_type'] = 'shows_db';
        !empty($shows) ? $page .= buildTable('L_DB', $shows, $topt) : null;
    }
    if (!isset($_GET['search_movies']) && !isset($_GET['search_shows']) && !empty(getPrefsItem('show_trending'))) {
        $topt['no_pages'] = 1;
        $results = mediadb_getTrending();

        if ($cfg['want_movies']) {
            $topt['view_type'] = 'movies_db';
            $page .= buildTable('L_TRENDING_MOVIES', $results['movies'], $topt);
        }
        if ($cfg['want_shows']) {
            $topt['view_type'] = 'shows_db';
            $page .= buildTable('L_TRENDING_SHOWS', $results['shows'], $topt);
        }
    }

    if (!isset($_GET['search_movies']) && !isset($_GET['search_shows']) && !empty(getPrefsItem('show_popular'))) {
        $topt['no_pages'] = 1;
        $results = mediadb_getPopular();
        if ($cfg['want_movies']) {
            $topt['view_type'] = 'movies_db';
            $page .= buildTable('L_POPULAR_MOVIES', $results['movies'], $topt);
        }
        if ($cfg['want_shows']) {
            $topt['view_type'] = 'shows_db';
            $page .= buildTable('L_POPULAR_SHOWS', $results['shows'], $topt);
        }
    }

    if (!isset($_GET['search_movies']) && !isset($_GET['search_shows']) && !empty(getPrefsItem('show_today_shows'))) {
        $topt['no_pages'] = 1;
        $results = mediadb_getTodayShows();
        $topt['view_type'] = 'shows_db';
        $page .= buildTable('L_TODAY_SHOWS', $results['shows'], $topt);
    }
    return $page;
}

function page_torrents() {
    global $frontend;

    (!empty($_GET['search_movies_torrents'])) ? $search_movies_torrents = Filter::getUtf8('search_movies_torrents') : $search_movies_torrents = '';
    (!empty($_GET['search_shows_torrents'])) ? $search_shows_torrents = Filter::getUtf8('search_shows_torrents') : $search_shows_torrents = '';

    $tdata['search_movies_word'] = $search_movies_torrents;
    $tdata['search_shows_word'] = $search_shows_torrents;

    $page = $frontend->getTpl('page_torrents', $tdata);

    if (!empty($search_movies_torrents)) {
        $search['words'] = trim($search_movies_torrents);
        $m_results = search_media_torrents('movies', $search, 'L_TORRENT');
        if (valid_array($m_results)) {
            $m_results = mix_media_res($m_results);
            $topt['view_type'] = 'movies_torrent';
            $topt['search_type'] = 'movies';
            $page .= buildTable('L_TORRENT', $m_results, $topt);
        } else {
            $box_msg['title'] = 'L_TORRENT';
            $box_msg['body'] = 'L_NOTHING_FOUND';
            $page .= $frontend->msgBox($box_msg);
        }
    }

    if (!empty($search_shows_torrents)) {
        $search['words'] = trim($search_shows_torrents);
        $m_results = search_media_torrents('shows', $search, 'L_TORRENT');
        if (valid_array($m_results)) {
            $m_results = mix_media_res($m_results);
            $topt['view_type'] = 'shows_torrent';
            $topt['search_type'] = 'shows';
            $page .= buildTable('L_TORRENT', $m_results, $topt);
        } else {
            $box_msg['title'] = 'L_TORRENT';
            $box_msg['body'] = 'L_NOTHING_FOUND';
            $page .= $frontend->msgBox($box_msg);
        }
    }

    return $page;
}

function page_wanted() {
    global $db, $trans, $frontend;

    $want = [];
    //Update wanted agains transmission
    !empty($trans) ? $trans->updateWanted() : null;

    if (isset($_POST['check_day'])) {
        $wanted_mfy = Filter::postInt('check_day');
        foreach ($wanted_mfy as $w_mfy_id => $w_mfy_value) {
            $day_check['day_check'] = $w_mfy_value;
            $db->updateItemById('wanted', $w_mfy_id, $day_check);
        }
    }

    isset($_GET['id']) ? $wanted_id = Filter::getInt('id') : $wanted_id = false;
    isset($_GET['media_type']) ? $wanted_type = Filter::getString('media_type') : $wanted_type = false;
    isset($_GET['delete']) && Filter::getInt('delete') ? $db->deleteItemById('wanted', Filter::getInt('delete')) : null;

    if ($wanted_id !== false && $wanted_type !== false && $wanted_type == 'movies') {
        wanted_movies($wanted_id);
    }

    $want['wanted_list'] = wanted_list();

    return !empty($want['wanted_list']) ? $frontend->getTpl('wanted', $want) : false;
}

function page_identify() {
    global $db, $cfg, $frontend;

    $media_type = Filter::getString('media_type');
    $id = Filter::getInt('identify');
    $id_all = Filter::getInt('identify_all');

    if ($media_type === false || ($id === false && $id_all === false)) {
        return $frontend->msgBox(['title' => 'L_ERROR', 'body' => '1A1002']);
    }

    if (empty($id)) {
        $id = $id_all;
        $tdata['identify_all'] = 1;
    } else {
        $tdata['identify_all'] = 0;
    }

    $library = 'library_' . $media_type;
    $item = $db->getItemById($library, $id);
    $tdata['head'] = '';

    /*
     * selected contain key: id in local db and value:themoviedb_id;
     */
    if (isset($_POST['identify']) && Filter::postInt('selected')) {
        $ident_pairs = Filter::postInt('selected');
        if (!empty($_POST['identify_all'])) {
            $results = $db->select($library, 'master', ['id' => ['value' => array_key_first($ident_pairs)]]);
            $item_master = $db->fetchAll($results);
            $results = $db->select($library, 'id', ['master' => ['value' => $item_master[0]['master']]]);
            $items = $db->fetchAll($results);
            $ident_pairs_all = [];
            foreach ($items as $item) {
                $ident_pairs_all[$item['id']] = $ident_pairs[array_key_first($ident_pairs)];
            }
            ident_by_idpairs($media_type, $ident_pairs_all);
        } else {
            ident_by_idpairs($media_type, $ident_pairs);
        }
        return $frontend->msgBox($msg = ['title' => 'L_SUCCESS', 'body' => 'L_ADDED_SUCCESSFUL']);
    }
    !empty($_POST['submit_title']) ? $submit_title = Filter::postUtf8('submit_title') : $submit_title = getFileTitle(basename($item['path']));

    $tdata['search_title'] = $submit_title;

    $item_selected = [];

    if (!empty($submit_title)) {
        ($media_type == 'movies') ? $db_media = mediadb_searchMovies($submit_title) : $db_media = mediadb_searchShows($submit_title);
        if (valid_array($db_media)) {
            $select = '';

            foreach ($db_media as $db_item) {
                if (!empty(Filter::postInt('selected')) && ($db_item['themoviedb_id'] == current(Filter::postInt('selected')))) {
                    $item_selected = $db_item;
                }
                if (!empty($db_item['release'])) {
                    $year = trim(substr($db_item['release'], 0, 4));
                }
                $title = $db_item['title'];
                !empty($year) ? $title .= ' (' . $year . ')' : null;
                $values[] = ['value' => $db_item['themoviedb_id'], 'name' => $title];
            }

            if (!empty(Filter::postInt('selected'))) {
                $conf_selected = current(Filter::postInt('selected'));
            } else {
                $conf_selected = '';
            }
            $select .= Html::select(['onChange' => 'this.form.submit()', 'class' => 'ident_select', 'selected' => $conf_selected, 'name' => 'selected[' . $id . ']'], $values);

            $tdata['select'] = $select;
        }
    }

    if (valid_array($item_selected)) {
        isset($item_selected['poster']) ? $tdata['selected_poster'] = $item_selected['poster'] : $tdata['selected_poster'] = $cfg['img_url'] . '/not_available.jpg';
        isset($item_selected['plot']) ? $tdata['selected_plot'] = $item_selected['plot'] : null;
    } else {
        if (valid_array($db_media)) {
            $first_item = current($db_media);
            isset($first_item['poster']) ? $tdata['selected_poster'] = $first_item['poster'] : $tdata['selected_poster'] = $cfg['img_url'] . '/not_available.jpg';
            isset($first_item['plot']) ? $tdata['selected_plot'] = $first_item['plot'] : null;
        }
    }

    return $frontend->getTpl('identify_adv', array_merge($item, $tdata));
}

function page_download() {
    global $db;

    $id = Filter::getInt('id');
    $media_type = Filter::getString('media_type');

    if (empty($id) || empty($media_type)) {
        exit();
    }
    $item = $db->getItemById('library_' . $media_type, $id);

    (!empty($item) && file_exists($item['path'])) ? send_file($item['path']) : null;

    exit();
}

function page_transmission() {
    global $trans, $frontend;

    if ($trans == false) {
        return false;
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $tid = Filter::postInt('tid');

        isset($_POST['start_all']) && !empty($trans) ? $trans->startAll() : null;
        isset($_POST['stop_all']) && !empty($trans) ? $trans->stopAll() : null;

        if (!empty($tid)) {
            isset($_POST['start']) && !empty($trans) ? $trans->start($tid) : null;
            isset($_POST['stop']) && !empty($trans) ? $trans->stop($tid) : null;
            isset($_POST['delete']) && !empty($trans) ? $trans->delete($tid) : null;
        }
    }

    if (!valid_array($transfers = $trans->getAll())) {
        return false;
    }

    $page = '';
    $tdata['body'] = '';

    foreach ($transfers as $transfer) {
        $transfer['status'] == 0 ? $tdata['show_start'] = 1 : $tdata['show_start'] = 0;
        $transfer['status'] != 0 && $transfer['status'] < 8 ? $tdata['show_stop'] = 1 : $tdata['show_stop'] = 0;

        $tdata['status_name'] = $trans->getStatusName($transfer['status']);
        $transfer['percentDone'] == 1 ? $tdata['percent'] = '100' : $tdata['percent'] = ((float) $transfer['percentDone']) * 100;
        $tdata['body'] .= $frontend->getTpl('transmission-row', array_merge($transfer, $tdata));
    }

    $page .= $frontend->getTpl('transmission-body', $tdata);

    return $page;
}

function page_config() {
    global $config;

    $page = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['submit_config'])) {
            $config_keys = Filter::postString('config_keys');
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
            $value = Filter::varString($value);
            if (isset($_POST['config_id'][array_key_first($_POST['config_add'])])) {
                $id = $_POST['config_id'][array_key_first($_POST['config_add'])];
            } else {
                $id = null;
            }
            empty($_POST['add_before'][array_key_first($_POST['config_add'])]) ? $before = 0 : $before = 1;
            $config->addCommaElement($key, trim($value), $id, $before);
        }
    }
    $page .= $config->display(Filter::getString('category'));

    return $page;
}

function page_login() {
    global $cfg, $db, $user, $frontend;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $dologin = 0;

        $username = Filter::postUsername('username');
        $password = Filter::postUsername('password');
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
                } else {
                    $user = [];
                }
            }
        }
    }

    $tdata = [];
    $result = $db->select('users');
    $users = $db->fetchAll($result);
    $page = '';
    $tdata['profiles'] = '';
    foreach ($users as $_user) {
        if ($_user['disable'] != 1 && $_user['hide_login'] != 1) {
            $tdata['profiles'] .= $frontend->getTpl('profile_box', array_merge($tdata, $_user));
        }
    }
    $page .= $frontend->getTpl('login', $tdata);
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
    global $db;

    $id = Filter::getInt('id');
    $media_type = Filter::getString('media_type');

    if (empty($id) || empty($media_type)) {
        exit();
    }
    $table = 'library_' . $media_type;
    $item = $db->getItemById($table, $id);
    if (!valid_array($item)) {
        exit();
    }
    if ($media_type == 'movies') {
        $m3u_playlist = get_pl_movies($item);
    } else {
        $m3u_playlist = get_pl_shows($item);
    }

    $header_title = $item['title'];
    header("Content-Type: video/mpegurl");
    header("Content-Disposition: attachment; filename=$header_title.m3u8");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo "#EXTM3U\r\n";
    echo $m3u_playlist;
    echo "#EXT-X-ENDLIST";
    exit(0);
}
