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
    if (!empty($user->isAdmin())) {
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
    $tdata['title'] = $LNG['L_IDENTIFIED'] . ': ' . strtoupper($user->getUsername());

    if (Filter::getInt('edit_profile')) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            (isset($_POST['cur_password']) && isset($_POST['new_password'])) ? $status_msg .= user_change_password() . '<br/>' : null;
            $status_msg .= user_change_prefs();
        }
        $tdata['content'] .= user_edit_profile();
    } else {
        $tdata['content'] .= Html::link(['class' => 'action_link'], '', $LNG['L_EDIT'], ['page' => 'index', 'edit_profile' => 1]);
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
    if (!empty($user->isAdmin())) {
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
    global $db, $frontend, $user;

    $id = Filter::getInt('id');
    $deletereg_id = Filter::getInt('deletereg_id');
    $view_type = Filter::getString('view_type');
    $media_type = Filter::getAzChar('media_type');
    $vid = Filter::getInt('vid');

    if (empty($id) || empty($view_type)) {
        return $frontend->msgBox($msg = ['title' => 'L_ERROR', 'body' => '1A1001']);
    }

    if ($view_type == 'movies_library') {
        $library = 'library_movies';
        $library_master = 'library_master_movies';
    } else if ($view_type == 'shows_library') {
        $library = 'library_shows';
        $library_master = 'library_master_shows';
    }

    if (!empty($vid) && !empty($media_type)) {
        $vitem = $db->getItemById($library, $vid);
        $results = $db->select('view_media', '*', ['file_hash' => ['value' => $vitem['file_hash']]], 'LIMIT 1');
        $view_data = $db->fetchAll($results);
        if (valid_array($view_data)) {
            $db->delete('view_media', ['id' => ['value' => $view_data[0]['id']]], 'LIMIT 1');
        } else {
            $master = $db->getItemById($library_master, $vitem['master']);

            $values['uid'] = $user->getId();
            $values['themoviedb_id'] = $master['themoviedb_id'];
            $values['file_hash'] = $vitem['file_hash'];
            $values['media_type'] = $media_type;
            if ($media_type == 'shows') {
                $values['season'] = $vitem['season'];
                $values['episode'] = $vitem['episode'];
            }
            $db->insert('view_media', $values);
        }
    }

    if (!empty($deletereg_id)) {

        $register_item = $db->getItemById($library, $deletereg_id);
        if (valid_array($register_item)) {
            $register_master_item = $db->getItemById($library_master, $register_item['master']);
            if (valid_array($register_master_item)) {
                $db->deleteItemById($library_master, $register_item['master']);
                $db->deleteItemsByField($library, 'master', $register_item['master']);
                return $frontend->msgBox($msg = ['title' => 'L_SUCCESS', 'body' => 'L_REGISTER_DELETED_SUCCESFUL']);
            }
        }
    }

    return view();
}

function page_view_group() {
    global $db, $frontend, $prefs;

    $id = Filter::getInt('id');
    $group_type = Filter::getInt('group_type');
    $media_type = Filter::getAzChar('media_type');
    $npage = Filter::getString('npage');

    empty($npage) ? $npage = 1 : null;

    $rows = $prefs->getPrefsItem('tresults_rows');
    $columns = $prefs->getPrefsItem('tresults_columns');
    $n_results = $rows * $columns;
    $npage == 1 ? $start = 0 : $start = ($npage - 1) * $n_results;

    if (empty($id) || empty($group_type) || empty($media_type)) {
        return false;
    }
    $library_master = 'library_master_' . $media_type;

    $results = $db->select('groups', null, ['type' => ['value' => 3], 'id' => ['value' => $id]], 'LIMIT 1');
    $collection = $db->fetchAll($results);

    if (!valid_array($collection)) {
        return false;
    }
    $type_id = $collection[0]['type_id'];
    $collection_items = $db->getItemsByField($library_master, 'collection', $type_id, "LIMIT $start,$n_results");

    if (!valid_array($collection_items)) {
        return false;
    }
    mark_masters_views($media_type, $collection_items);

    $nitems = $db->qSingle("SELECT COUNT(*) FROM $library_master WHERE collection = $type_id");

    $pager_opt['npage'] = $npage;
    $pager_opt['nitems'] = $nitems;
    $pager_opt['media_type'] = $media_type;
    $pager_opt['get_params']['media_type'] = $media_type;
    $pager_opt['get_params']['group_type'] = 3;
    $pager_opt['get_params']['id'] = $id;

    $fcollection_items = $frontend->getPager($pager_opt);
    $table_opt['head'] = $media_type;
    $table_opt['media_type'] = $media_type;
    $table_opt['view_type'] = $media_type . '_library';
    $table_opt['page'] = 'view_group';
    $table_opt['npage'] = $npage;

    $fcollection_items .= $frontend->buildTable($collection_items, $table_opt);

    $collection[0]['item_list'] = $fcollection_items;
    return $frontend->getTpl('view_group', $collection[0]);
}

function page_library() {
    global $cfg, $prefs;

    $page_library = '';

    (isset($_POST['rebuild_movies'])) ? rebuild('movies', $cfg['MOVIES_PATH']) : null;
    (isset($_POST['rebuild_shows'])) ? rebuild('shows', $cfg['SHOWS_PATH']) : null;

    if (($cfg['want_movies']) && ( $_GET['page'] == 'library' || $_GET['page'] == 'library_movies')) {
        if ($prefs->getPrefsItem('show_collections')) {
            $page_library .= show_collections();
        } else {
            $page_library .= show_my_media('movies');
        }
    }
    if (($cfg['want_shows']) && ($_GET['page'] == 'library' || $_GET['page'] == 'library_shows')) {
        $page_library .= show_my_media('shows');
    }

    return $page_library;
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
    global $cfg, $frontend, $prefs;

    (!empty($_GET['search_movies'])) ? $search_movies = Filter::getUtf8('search_movies') : $search_movies = '';
    (!empty($_GET['search_shows'])) ? $search_shows = Filter::getUtf8('search_shows') : $search_shows = '';

    $tdata['search_movies_word'] = $search_movies;
    $tdata['search_shows_word'] = $search_shows;

    $page_tmdb = $frontend->getTpl('page_tmdb', $tdata);

    if (!empty($search_movies)) {
        $movies = mediadb_searchMovies(trim($search_movies));
        $topt['search_type'] = 'movies';
        $topt['view_type'] = 'movies_db';
        !empty($movies) ? $page_tmdb .= buildTable('L_DB', $movies, $topt) : null;
    }

    if (!empty($search_shows)) {
        $shows = mediadb_searchShows(trim($search_shows));
        $topt['search_type'] = 'shows';
        $topt['view_type'] = 'shows_db';
        !empty($shows) ? $page_tmdb .= buildTable('L_DB', $shows, $topt) : null;
    }
    if (empty($_GET['search_movies']) && empty($_GET['search_shows']) && !empty($prefs->getPrefsItem('show_trending'))) {
        $topt['no_pages'] = 1;
        $results = mediadb_getTrending();

        if ($cfg['want_movies']) {
            $topt['view_type'] = 'movies_db';
            $page_tmdb .= buildTable('L_TRENDING_MOVIES', $results['movies'], $topt);
        }
        if ($cfg['want_shows']) {
            $topt['view_type'] = 'shows_db';
            $page_tmdb .= buildTable('L_TRENDING_SHOWS', $results['shows'], $topt);
        }
    }

    if (empty($_GET['search_movies']) && empty($_GET['search_shows']) && !empty($prefs->getPrefsItem('show_popular'))) {
        $topt['no_pages'] = 1;
        $results = mediadb_getPopular();
        if ($cfg['want_movies']) {
            $topt['view_type'] = 'movies_db';
            $page_tmdb .= buildTable('L_POPULAR_MOVIES', $results['movies'], $topt);
        }
        if ($cfg['want_shows']) {
            $topt['view_type'] = 'shows_db';
            $page_tmdb .= buildTable('L_POPULAR_SHOWS', $results['shows'], $topt);
        }
    }

    if (empty($_GET['search_movies']) && empty($_GET['search_shows']) && !empty($prefs->getPrefsItem('show_today_shows'))) {
        $topt['no_pages'] = 1;
        $results = mediadb_getTodayShows();
        $topt['view_type'] = 'shows_db';
        $page_tmdb .= buildTable('L_TODAY_SHOWS', $results['shows'], $topt);
    }
    return $page_tmdb;
}

function page_torrents() {
    global $frontend, $prefs;

    (!empty($_GET['search_movies_torrents'])) ? $search_movies_torrents = Filter::getUtf8('search_movies_torrents') : $search_movies_torrents = '';
    (!empty($_GET['search_shows_torrents'])) ? $search_shows_torrents = Filter::getUtf8('search_shows_torrents') : $search_shows_torrents = '';

    $tdata['search_movies_word'] = $search_movies_torrents;
    $tdata['search_shows_word'] = $search_shows_torrents;

    $page_torrents = $frontend->getTpl('page_torrents', $tdata);

    if (!empty($search_movies_torrents)) {
        $search['words'] = trim($search_movies_torrents);
        $m_results = search_media_torrents('movies', $search, 'L_TORRENT');

        if (valid_array($m_results)) {
            torrents_filters($m_results);
        }

        if (valid_array($m_results)) {
            usort($m_results, function ($a, $b) {
                return $b['id'] - $a['id'];
            });
            $m_results = mix_media_res($m_results);
            $topt['view_type'] = 'movies_torrent';
            $topt['search_type'] = 'movies';
            $page_torrents .= buildTable('L_TORRENT', $m_results, $topt);
        } else {
            $box_msg['title'] = 'L_TORRENT';
            $box_msg['body'] = 'L_NOTHING_FOUND';
            $page_torrents .= $frontend->msgBox($box_msg);
        }
    }

    if (!empty($search_shows_torrents)) {
        $search['words'] = trim($search_shows_torrents);
        $m_results = search_media_torrents('shows', $search, 'L_TORRENT');

        if (valid_array($m_results)) {
            torrents_filters($m_results);
        }

        if (valid_array($m_results)) {
            usort($m_results, function ($a, $b) {
                return strcmp($b['title'], $a['title']);
            });
            $m_results = mix_media_res($m_results);
            $topt['view_type'] = 'shows_torrent';
            $topt['search_type'] = 'shows';
            $page_torrents .= buildTable('L_TORRENT', $m_results, $topt);
        } else {
            $box_msg['title'] = 'L_TORRENT';
            $box_msg['body'] = 'L_NOTHING_FOUND';
            $page_torrents .= $frontend->msgBox($box_msg);
        }
    }

    if (empty($search_movies_torrents) && empty($search_shows_torrents)) {
        if ($prefs->getPrefsItem('movies_cached')) {
            $page_torrents .= show_cached_torrents('movies');
        }
        if ($prefs->getPrefsItem('shows_cached')) {
            $page_torrents .= show_cached_torrents('shows');
        }
    }

    return $page_torrents;
}

function page_wanted() {
    global $db, $cfg, $trans, $frontend;

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
    $want = wanted_list();

    if (!empty($cfg['torrent_quality_prefs'])) {
        $want['quality_tags'] = '';
        foreach ($cfg['torrent_quality_prefs'] as $quality) {
            $want['quality_tags'] .= Html::span(['class' => 'tag_quality'], $quality);
        }
    }

    if (!empty($cfg['torrent_ignore_prefs'])) {
        $want['ignore_tags'] = '';
        foreach ($cfg['torrent_ignore_prefs'] as $ignores) {
            $want['ignore_tags'] .= Html::span(['class' => 'tag_ignore'], $ignores);
        }
    }

    if (!empty($cfg['torrent_require_prefs'])) {
        $want['require_tags'] = '';
        foreach ($cfg['torrent_require_prefs'] as $require) {
            $want['require_tags'] .= Html::span(['class' => 'tag_require'], $require);
        }
    }

    if (!empty($cfg['torrent_require_or_prefs'])) {
        $want['require_or_tags'] = '';
        foreach ($cfg['torrent_require_or_prefs'] as $or_require) {
            $want['require_or_tags'] .= Html::span(['class' => 'tag_require'], $or_require);
        }
    }

    return !empty($want) ? $frontend->getTpl('wanted', $want) : false;
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
    $ident_item = $db->getItemById($library, $id);
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
            /* we add all register without master and same title */
            $results = $db->query('SELECT id,file_name FROM ' . $library . ' WHERE master IS NULL');
            $items = $db->fetchAll($results);
            if (valid_array($items)) {
                foreach ($items as $item) {
                    if (getFileTitle($item['file_name']) == getFileTitle($ident_item['file_name'])) {
                        $ident_pairs_final[$item['id']] = $ident_pairs[array_key_first($ident_pairs)];
                    }
                }
            } else {
                $ident_pairs_final = $ident_pairs;
            }
            ident_by_idpairs($media_type, $ident_pairs_final);
        }
        return $frontend->msgBox($msg = ['title' => 'L_SUCCESS', 'body' => 'L_ADDED_SUCCESSFUL']);
    }
    !empty($_POST['submit_title']) ? $submit_title = Filter::postUtf8('submit_title') : $submit_title = getFileTitle(basename($ident_item['path']));

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

    return $frontend->getTpl('identify_adv', array_merge($ident_item, $tdata));
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

    $tdata['body'] = '';

    foreach ($transfers as $transfer) {
        $transfer['status'] == 0 ? $tdata['show_start'] = 1 : $tdata['show_start'] = 0;
        $transfer['status'] != 0 && $transfer['status'] < 8 ? $tdata['show_stop'] = 1 : $tdata['show_stop'] = 0;

        $tdata['status_name'] = $trans->getStatusName($transfer['status']);
        $transfer['percentDone'] == 1 ? $tdata['percent'] = '100' : $tdata['percent'] = ((float) $transfer['percentDone']) * 100;
        $tdata['body'] .= $frontend->getTpl('transmission-row', array_merge($transfer, $tdata));
    }


    return $frontend->getTpl('transmission-body', $tdata);
}

function page_config() {
    global $config;

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

    return $config->display(Filter::getString('category'));
}

function page_login() {
    global $cfg, $user, $frontend;

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
                $userid = $user->checkUser($username, $password);
                if (!empty($userid) && $userid > 0) {
                    $user->setUser($userid);
                    header("Location: {$cfg['REL_PATH']} ");
                    exit();
                }
            }
        }
    }

    $tdata = [];
    $users_db = $user->getProfiles();

    $tdata['profiles'] = '';
    foreach ($users_db as $db_user) {
        if ($db_user['disable'] != 1 && $db_user['hide_login'] != 1) {
            $tdata['username'] = $db_user['username'];
            $tdata['profiles'] .= $frontend->getTpl('profile_box', $tdata);
        }
    }

    return $frontend->getTpl('login', $tdata);
}

function page_logout() {
    global $cfg;

    $_SESSION['uid'] = 0;
    ($_COOKIE) ? setcookie("uid", null, -1) : null;
    ($_COOKIE) ? setcookie("sid", null, -1) : null;
    session_regenerate_id();
    session_destroy();
    header("Location: {$cfg['REL_PATH']} ");
    exit(0);
}

function page_localplayer() {
    global $db, $frontend;

    $id = Filter::getInt('id');
    $mid = Filter::getInt('mid');
    $media_type = Filter::getString('media_type');

    if ((empty($id) && empty($mid)) || empty($media_type)) {
        $frontend->msgPage($msg = ['title' => 'L_ERROR', 'body' => '1A1003']);
        return false;
    }
    $library = 'library_' . $media_type;
    $library_master = 'library_master_' . $media_type;

    if (!empty($id)) {

        $item = $db->getItemById($library, $id);
        if (!valid_array($item)) {
            $frontend->msgPage($msg = ['title' => 'L_ERROR', 'body' => 'L_ERR_ITEM_NOT_FOUND']);
            return false;
        }
        if ($media_type == 'movies') {
            $m3u_playlist = get_pl_movies($item);
        } else {
            $m3u_playlist = get_pl_shows($item);
        }
        $header_title = ucwords(clean_title($item['file_name']));
    } else if (!empty($mid)) {
        $master_item = $db->getItemById($library_master, $mid);
        if (!valid_array($master_item)) {
            $frontend->msgPage($msg = ['title' => 'L_ERROR', 'body' => 'L_ERR_ITEM_NOT_FOUND']);
            return false;
        }
        $m3u_playlist = get_pl_next_media($master_item, $media_type);
        $header_title = ucwords($master_item['title']);
    }

    header("Content-Type: video/mpegurl");
    header("Content-Disposition: attachment; filename=$header_title.m3u");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo "#EXTM3U\r\n";
    echo $m3u_playlist;
    echo "#EXT-X-ENDLIST";
    exit(0);
}
