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
    global $cfg, $db, $user, $LNG, $log;

    //POST ADMIN
    if (isset($_POST['clear_disabled']) && $user->isAdmin()) {
        foreach ($cfg['jackett_indexers'] as $indexer) {
            $indexer_disabled = $indexer . '_disable';
            $db->delete('preferences', ['pref_name' => ['value' => $indexer_disabled]], 'LIMIT 1');
        }
    }
    if (isset($_POST['clear_search_cache']) && $user->isAdmin()) {
        $db->delete('jackett_search_movies_cache');
        $db->delete('jackett_search_shows_cache');
        $db->delete('search_movies_cache');
        $db->delete('search_shows_cache');
    }

    if (isset($_POST['force_fix_perms']) && $user->isAdmin()) {
        //TODO
        //set force_fix_perms, cli must exec fix_perms if force_fix_perms is set
        //cli must clear force_fix_perms
    }

    //Father TPL
    $main['templates'] = [];

    // User Profile

    $profile_container_vars = [];
    $profile_container_vars['title'] = $LNG['L_PROFILE'];
    $profile_container_vars['content'] = '';
    $profile_container_vars['status_msg'] = '';

    //User Profile

    $profile_vars = [];
    $profile_vars['username'] = strtoupper($user->getUsername());
    if (Filter::getInt('edit_profile')) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            (isset($_POST['cur_password']) && isset($_POST['new_password'])) ? $profile_container_vars['status_msg'] .= user_change_password() . '<br/>' : null;
            $profile_container_vars['status_msg'] .= user_change_prefs();
        }
        $main['templates'][] = user_edit_profile();
    } else {
        $profile_vars['edit_profile_btn'] = $LNG['L_EDIT'];
    }
    $profile_vars['logoutbtn'] = $LNG['L_LOGOUT'];

    //Adding profiles templates

    $main['templates'][] = [
        'name' => 'user_profile',
        'tpl_file' => 'user_profile',
        'tpl_pri' => 16,
        'tpl_place' => 'profile_container',
        'tpl_place_var' => 'content',
        'tpl_vars' => $profile_vars
    ];

    //Profile Container
    $main['templates'][] = [
        'name' => 'profile_container',
        'tpl_file' => 'home-item',
        'tpl_pri' => 15,
        'tpl_place' => 'homepage',
        'tpl_place_var' => 'col1',
        'tpl_vars' => $profile_container_vars,
    ];

    //
    // Config
    //
    if (!empty($user->isAdmin())) {
        $main['templates'][] = [
            'name' => 'admin_container',
            'tpl_file' => 'home-item',
            'tpl_pri' => 12,
            'tpl_place' => 'homepage',
            'tpl_place_var' => 'col1',
            'tpl_vars' => [
                'title' => $LNG['L_ADMINISTRATION'],
            ]
        ];

        $admin_vars = [];
        $admin_vars['clear_disable'] = 1;
        $admin_vars['clear_disable_title'] = $LNG['L_CLEAR_DISABLE'];
        $admin_vars['clear_search_cache'] = 1;
        $admin_vars['clear_search_cache_title'] = $LNG['L_CLEAR_SEARCH_CACHE'];
        $admin_vars['force_fix_perms'] = 1;
        $admin_vars['force_fix_perms_title'] = $LNG['L_FIX_PERMS'];
        $admin_vars['config_btn'] = 1;
        $admin_vars['config_btn_title'] = $LNG['L_CONFIG'];

        $main['templates'][] = [
            'name' => 'admin_profile',
            'tpl_file' => 'admin_profile',
            'tpl_pri' => 13,
            'tpl_place' => 'admin_container',
            'tpl_place_var' => 'content',
            'tpl_vars' => $admin_vars,
        ];
    }

    // User managament
    if (!empty($user->isAdmin())) {
        $main['templates'][] = new_user();
        $main['templates'][] = show_users();
        $main['templates'][] = user_management();
    }

    //
    // Disks
    //

    $disks_vars = [];
    $libstats = getLibraryStats();
    if (is_array($libstats)) {
        $disks_vars = array_merge($disks_vars, $libstats);
    }

    $main['templates'][] = [
        'name' => 'disks',
        'tpl_file' => 'harddisk',
        'tpl_pri' => 9,
        'tpl_place' => 'disks_container',
        'tpl_place_var' => 'content',
        'tpl_vars' => $disks_vars
    ];

    $main['templates'][] = [
        'name' => 'disks_container',
        'tpl_file' => 'home-item',
        'tpl_pri' => 8,
        'tpl_place' => 'homepage',
        'tpl_place_var' => 'col1',
        'tpl_vars' => ['title' => '']
    ];

    //
    // States Messages
    //

    isset($_POST['clear_state']) ? $log->clearStateMsgs() : null;

    $statusmsg_vars = [];
    $statusmsg_vars['clear_title'] = $LNG['L_CLEAR'];

    $status_msgs = $log->getStatusMsg();

    if (!empty($status_msgs) && (count($status_msgs) > 0)) {
        $status_msgs[0]['first'] = 1;
        foreach ($status_msgs as $stid => $status_msg) {
            $status_msgs[$stid]['created_frmt'] = strftime("%d %h %X", strtotime($status_msg['created']));
        }
    } else {
        $status_msgs = [];
    }

    $statusmsg_vars['msg'] = $status_msgs;

    $main['templates'][] = [
        'name' => 'statusmsg_item',
        'tpl_file' => 'statusmsg_item',
        'tpl_pri' => 30,
        'tpl_place' => 'statusmsg_container',
        'tpl_place_var' => 'content',
        'tpl_vars' => $statusmsg_vars
    ];

    $main['templates'][] = [
        'name' => 'statusmsg_container',
        'tpl_file' => 'home-item',
        'tpl_pri' => 29,
        'tpl_place' => 'homepage',
        'tpl_place_var' => 'col2',
        'tpl_vars' => [
            'title' => $LNG['L_STATE_MSG'],
            'main_class' => 'home_state_msg',
        ]
    ];

    //
    // LATEST info
    //
    //TODO add upper template / remove implode
    $latest_ary = getfile_ary('LATEST');

    $latest_container_vars = [];
    $latest_container_vars['title'] = $LNG['L_NEWS'];
    $latest_container_vars['main_class'] = 'home_news';

    if (!empty($latest_ary)) {
        $latest_ary = array_slice($latest_ary, 2);
        $latest_container_vars['content'] = implode('<br/>', $latest_ary);
    } else {
        $latest_container_vars['content'] = [];
    }

    $main['templates'][] = [
        'name' => 'latest_container',
        'tpl_file' => 'home-item',
        'tpl_pri' => 25,
        'tpl_place' => 'homepage',
        'tpl_place_var' => 'col2',
        'tpl_vars' => $latest_container_vars,
    ];

    //
    // LOGS
    //
    //TODO add upper template / remove implode
    isset($_POST['clear_log']) ? file_put_contents('cache/log/trackerm.log', '') : null;

    $logs_container_tpl = [
        'name' => 'logs_container',
        'tpl_file' => 'home-item',
        'tpl_pri' => 10,
        'tpl_place' => 'homepage',
        'tpl_place_var' => 'col2',
        'tpl_vars' => [],
    ];

    $logs_container_tpl['tpl_vars']['title'] = $LNG['L_LOGS'];
    $logs_container_tpl['tpl_vars']['main_class'] = 'home_log';

    $logs_ary = getfile_log('cache/log/trackerm.log');
    if (!empty($logs_ary)) {
        $logs_ary = array_reverse($logs_ary);
        $logs_container_tpl['tpl_vars']['content'] = implode('<br/>', $logs_ary);
    }

    $main['templates'][] = $logs_container_tpl;

    //
    // Starting Info
    //

    $main['templates'][] = [
        'name' => 'starting_container',
        'tpl_file' => 'home-item',
        'tpl_pri' => 8,
        'tpl_place' => 'homepage',
        'tpl_place_var' => 'col2',
        'tpl_vars' => [
            'title' => $LNG['L_STARTING'],
            'main_class' => 'home_log',
            'content' => getfile('STARTING.' . substr($cfg['LANG'], 0, 2)),
        ],
    ];

    //FIN
    //Parent

    $main['templates'][] = [
        'name' => 'homepage',
        'tpl_file' => 'home-page',
        'tpl_pri' => 0,
        'tpl_vars' => [],
    ];

    return $main;
}

function page_view() {
    global $db, $user;

    $id = Filter::getInt('id');
    $view_type = Filter::getString('view_type');
    $media_type = Filter::getAzChar('media_type');
    $vid = Filter::getInt('vid');

    if (empty($id) || empty($view_type)) {
        return ['msg_type' => 1, 'title' => 'L_ERROR', 'body' => '1A1001'];
    }

    if ($view_type == 'movies_library') {
        $library = 'library_movies';
        $library_master = 'library_master_movies';
    } else if ($view_type == 'shows_library') {
        $library = 'library_shows';
        $library_master = 'library_master_shows';
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $dfile_id = Filter::postInt('file_id');
        $dfile_master_id = Filter::postInt('file_master');
        $dfile_opt = Filter::postInt('delete_opt');
        $dmedia_type = Filter::postAzChar('media_type');
        if (!empty($dmedia_type) && !empty($dfile_opt)) {
            //Register
            if ($dfile_opt == 1 && !empty($dfile_master_id)) {
                delete_register($dfile_master_id, $dmedia_type);
                return ['msg_type' => 1, 'title' => 'L_SUCCESS', 'body' => 'L_REGISTER_DELETED_SUCCESFUL'];
            }
            //FILE
            if ($dfile_opt == 2 && !empty($dfile_master_id)) {
                $return = delete_file($dfile_id, $dfile_master_id, $dmedia_type);
                if ($return === 'SUCCESS_NOMASTER') {
                    return ['msg_type' => 1, 'title' => 'L_SUCCESS', 'body' => 'L_REGISTER_DELETED_SUCCESFUL'];
                }
            }

            //FILES
            if ($dfile_opt == 3 && !empty($dfile_master_id)) {
                $return = delete_files($dfile_master_id, $dmedia_type);
                if ($return === 'SUCCESS_NOMASTER') {
                    return ['msg_type' => 1, 'title' => 'L_SUCCESS', 'body' => 'L_REGISTER_DELETED_SUCCESFUL'];
                }
            }
        }
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

    return view();
}

function page_view_group() {
    global $db, $prefs;

    $id = Filter::getInt('id');
    $group_type = Filter::getInt('group_type');
    $media_type = Filter::getAzChar('media_type');
    $npage = Filter::getString('npage');

    $collections['template'] = [];

    $search_type = Filter::getString('search_type');
    empty($npage) || (!empty($search_type) && $search_type !== $media_type) ? $npage = 1 : null;

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

    //TODO: Pager
    //$nitems = $db->qSingle("SELECT COUNT(*) FROM $library_master WHERE collection = $type_id");
    /*
      $pager_opt['npage'] = $npage;
      $pager_opt['nitems'] = $nitems;
      $pager_opt['media_type'] = $media_type;
      $pager_opt['get_params']['media_type'] = $media_type;
      $pager_opt['get_params']['group_type'] = 3;
      $pager_opt['get_params']['id'] = $id;

      //$fcollection_items = $frontend->getPager($pager_opt);
      $table_opt['head'] = $media_type;
      $table_opt['media_type'] = $media_type;
      $table_opt['view_type'] = $media_type . '_library';
      $table_opt['page'] = 'view_group';
      $table_opt['npage'] = $npage;
     */
    //$fcollection_items .= $frontend->buildTable($collection_items, $table_opt);
    //$collection[0]['item_list'] = $fcollection_items;
    //return $frontend->getTpl('view_group', $collection[0]);

    $group_items_tpl = [
        'name' => 'group_items',
        'tpl_file' => 'items_table',
        'tpl_pri' => 10,
        'tpl_place' => 'view_group',
        'tpl_place_var' => 'item_list',
        'tpl_vars' => $collection_items,
        'tpl_common_vars' => [
            'view_type' => $media_type . '_library',
            'media_type' => $media_type,
            'tpl_items_break' => $columns,
        ]
    ];

    $collections['templates'][] = $group_items_tpl;

    $view_group_tpl = [
        'name' => 'view_group',
        'tpl_file' => 'view_group',
        'tpl_pri' => 0,
        'tpl_vars' => $collection[0],
    ];

    $collections['templates'][] = $view_group_tpl;

    return $collections;
}

function page_view_genres() {
    global $prefs, $db, $LNG;

    $id = Filter::getInt('id');
    $media_type = Filter::getAzChar('media_type');
    $npage = Filter::getString('npage');
    $page = Filter::getString('page');
    $page_genres['templates'] = [];

    $search_type = Filter::getString('search_type');
    empty($npage) || (!empty($search_type) && $search_type !== $media_type) ? $npage = 1 : null;

    $rows = $prefs->getPrefsItem('tresults_rows');
    $columns = $prefs->getPrefsItem('tresults_columns');
    $n_results = $rows * $columns;
    $npage == 1 ? $start = 0 : $start = ($npage - 1) * $n_results;

    if (empty($id) || empty($media_type)) {
        return false;
    }
    $library_master = 'library_master_' . $media_type;

    $genre_like = '[' . $id . ']';
    $results = $db->query("SELECT COUNT(*) as total FROM $library_master WHERE genres LIKE '%$genre_like%'");
    $nitems_res = $db->fetch($results);
    $nitems = $nitems_res['total'];

    $results = $db->query("SELECT * FROM $library_master WHERE genres LIKE '%$genre_like%' LIMIT $start, $n_results");
    $genre_items = $db->fetchAll($results);

    mark_masters_views($media_type, $genre_items);

    $pager_opt['npage'] = $npage;
    $pager_opt['nitems'] = $nitems;
    $pager_opt['media_type'] = $media_type;
    $pager_opt['num_table_objs'] = $nitems;
    $pager_opt['pager_place'] = 'view_genres_container';
    $pager_opt['link_options'] = ['id' => $id, 'media_type' => $media_type];

    $table_opt['tpl_items_break'] = $columns;
    $table_opt['head'] = $media_type;
    $table_opt['media_type'] = $media_type;
    $table_opt['view_type'] = $media_type . '_library';
    $table_opt['page'] = $page;
    $table_opt['npage'] = $npage;

    $page_genres['templates'][] = get_pager(array_merge($table_opt, $pager_opt));

    $genres_tpl = [
        'name' => 'view_genres',
        'tpl_file' => 'items_table',
        'tpl_pri' => 2,
        'tpl_place' => 'view_genres_container',
        'tpl_place_var' => 'items',
        'tpl_vars' => $genre_items,
        'tpl_common_vars' => $table_opt
    ];

    $page_genres['templates'][] = [
        'name' => 'view_genres_container',
        'tpl_file' => 'items_table_container',
        'tpl_pri' => 0,
        'tpl_vars' => [
            'head' => $LNG['L_' . strtoupper($media_type)],
        ]
    ];

    $page_genres['templates'][] = $genres_tpl;
    return $page_genres;
}

function page_view_director() {
    return page_view_name('director');
}

function page_view_cast() {
    return page_view_name('cast');
}

function page_view_writer() {
    return page_view_name('writer');
}

function page_view_name(string $name_type) {
    global $prefs, $db, $LNG;

    $name = Filter::getUtf8('name');
    $media_type = Filter::getAzChar('media_type');
    $npage = Filter::getString('npage');
    $page_names['templates'] = [];
    $page = Filter::getString('page');

    empty($npage) ? $npage = 1 : null;

    $rows = $prefs->getPrefsItem('tresults_rows');
    $columns = $prefs->getPrefsItem('tresults_columns');
    $n_results = $rows * $columns;
    $npage == 1 ? $start = 0 : $start = ($npage - 1) * $n_results;

    if (empty($name) || empty($media_type) || empty($name_type)) {
        return false;
    }
    $field_name = $name_type;

    $library_master = 'library_master_' . $media_type;

    $q_name = $db->escape($name);
    $results = $db->query("SELECT COUNT(*) as total FROM $library_master WHERE \"$field_name\" LIKE '%$q_name%'");
    $nitems_res = $db->fetch($results);
    $nitems = $nitems_res['total'];

    $results = $db->query("SELECT * FROM $library_master WHERE \"$field_name\" LIKE '%$q_name%' LIMIT $start, $n_results");
    $names_items = $db->fetchAll($results);

    mark_masters_views($media_type, $names_items);

    $pager_opt['npage'] = $npage;
    $pager_opt['nitems'] = $nitems;
    $pager_opt['page'] = $page;
    $pager_opt['num_table_objs'] = $nitems;
    $pager_opt['pager_place'] = $media_type . '_viewnames_container';
    $pager_opt['link_options'] = ['name' => $name, 'media_type' => $media_type];

    $table_opt['head'] = $media_type;
    $table_opt['media_type'] = $media_type;
    $table_opt['view_type'] = $media_type . '_library';
    $table_opt['npage'] = $npage;
    $table_opt['tpl_items_break'] = $columns;

    $page_names['templates'][] = get_pager(array_merge($table_opt, $pager_opt));

    $page_names['templates'][] = [
        'name' => 'items_names_' . $media_type,
        'tpl_file' => 'items_table',
        'tpl_pri' => 5,
        'tpl_place' => $media_type . '_viewnames_container',
        'tpl_place_var' => 'items',
        'tpl_vars' => $names_items,
        'tpl_common_vars' => $table_opt,
    ];

    $page_names['templates'][] = [
        'name' => $media_type . '_viewnames_container',
        'tpl_file' => 'items_table_container',
        'tpl_pri' => 0,
        'tpl_place_var' => null,
        'tpl_place' => null,
        'tpl_vars' => [
            'head' => $LNG['L_' . strtoupper($media_type)],
        ]
    ];

    return $page_names;
}

function page_library() {
    global $cfg, $prefs, $LNG;

    $library = [];
    $library['templates'] = [];

    (isset($_POST['rebuild_movies'])) ? rebuild('movies', $cfg['MOVIES_PATH']) : null;
    (isset($_POST['rebuild_shows'])) ? rebuild('shows', $cfg['SHOWS_PATH']) : null;

    if (($cfg['want_movies']) && ( $_GET['page'] == 'library' || $_GET['page'] == 'library_movies')) {
        if (empty($prefs->getPrefsItem('show_collections'))) {
            $movies_templates = show_my_media('movies');
            $library['templates'] = array_merge($library['templates'], $movies_templates['templates']);
            //var_dump($library);
        } else {
            $sel_col = $prefs->getPrefsItem('show_collections');
            if ($sel_col == 1) {
                $collections_templates = show_collections();
                $library['templates'] = array_merge($library['templates'], $collections_templates['templates']);
            } else if ($sel_col == 2) {
                $genres_templates = show_genres('movies');
                $library['templates'] = array_merge($library['templates'], $genres_templates['templates']);
            } else if ($sel_col == 3) {
                $directors_templates = show_directors('movies');
                $library['templates'] = array_merge($library['templates'], $directors_templates['templates']);
            } else if ($sel_col == 4) {
                $cast_templates = show_cast('movies');
                $library['templates'] = array_merge($library['templates'], $cast_templates['templates']);
            } else if ($sel_col == 5) {
                $writer_templates = show_writer('movies');
                $library['templates'] = array_merge($library['templates'], $writer_templates['templates']);
            }
        }

        //Identify Movies Templates
        $ident_templates = show_identify_media('movies');
        if ($ident_templates && is_array($ident_templates['templates'])) {
            $library['templates'] = array_merge($library['templates'], $ident_templates['templates']);
        }         
        //Main Show Movies Template
        $library['templates'][] = [
            'name' => 'movies_library',
            'tpl_file' => 'items_table_container',
            'tpl_pri' => 4,
            'tpl_place_var' => null,
            'tpl_place' => null,
            'tpl_vars' => [
                'head' => $topt['head'] = strtoupper($LNG['L_MOVIES']),
                'items' => []
            ]
        ];
    }

    if (($cfg['want_shows']) && ($_GET['page'] == 'library' || $_GET['page'] == 'library_shows')) {
        if (empty($prefs->getPrefsItem('show_collections'))) {
            $shows_templates = show_my_media('shows');
            $library['templates'] = array_merge($library['templates'], $shows_templates['templates']);
        } else {
            $sel_col = $prefs->getPrefsItem('show_collections');
            if ($sel_col == 1) {
                $shows_templates = show_my_media('shows');
                $library['templates'] = array_merge($library['templates'], $shows_templates['templates']);
            } else if ($sel_col == 2) {
                $genres_templates = show_genres('shows');
                $library['templates'] = array_merge($library['templates'], $genres_templates['templates']);
            } else if ($sel_col == 3) {
                $directors_templates = show_directors('shows');
                $library['templates'] = array_merge($library['templates'], $directors_templates['templates']);
            } else if ($sel_col == 4) {
                $cast_templates = show_cast('shows');
                $library['templates'] = array_merge($library['templates'], $cast_templates['templates']);
            } else if ($sel_col == 5) {
                $writer_templates = show_writer('shows');
                $library['templates'] = array_merge($library['templates'], $writer_templates['templates']);
            }
        }

        //Identify Movies Templates
        $ident_templates = show_identify_media('shows');
        if ($ident_templates && is_array($ident_templates['templates'])) {
            $library['templates'] = array_merge($library['templates'], $ident_templates['templates']);
        }
        //Main Show Shows Template
        $library['templates'][] = [
            'name' => 'shows_library',
            'tpl_file' => 'items_table_container',
            'tpl_pri' => 4,
            'tpl_place_var' => null,
            'tpl_place' => null,
            'tpl_vars' => [
                'head' => $topt['head'] = strtoupper($LNG['L_SHOWS']),
                'items' => ''
            ]
        ];
    }

    //var_dump($library);
    return $library;
}

function page_news() {
    global $cfg, $LNG;

    $page_news['templates'] = [];

    if (($cfg['want_movies']) && ($_GET['page'] == 'news' || $_GET['page'] == 'new_movies')) {
        $movies_templates = page_new_media('movies');
        
        $page_news['templates'][] = [
            'name' => 'movies_torrents',
            'tpl_file' => 'items_table_container',
            'tpl_pri' => 4,
            'tpl_place_var' => null,
            'tpl_place' => null,
            'tpl_vars' => [
                'head' => $topt['head'] = strtoupper($LNG['L_MOVIES']),
                'items' => []
            ]
        ];
        $page_news['templates'] = array_merge($page_news['templates'], $movies_templates['templates']);
    }

    if (($cfg['want_shows']) && ($_GET['page'] == 'news' || $_GET['page'] == 'new_shows')) {
        $shows_templates = page_new_media('shows');

        $page_news['templates'][] = [
            'name' => 'shows_torrents',
            'tpl_file' => 'items_table_container',
            'tpl_pri' => 4,
            'tpl_vars' => [
                'head' => $topt['head'] = strtoupper($LNG['L_SHOWS']),
                'items' => []
            ]
        ];

        $page_news['templates'] = array_merge($page_news['templates'], $shows_templates['templates']);
    }

    return $page_news;
}

function page_tmdb() {
    global $cfg, $prefs, $LNG;

    $page_tmdb['templates'] = [];

    $search_type = Filter::getString('search_type');
    $columns = $prefs->getPrefsItem('tresults_columns');
    $rows = $prefs->getPrefsItem('tresults_rows');
    $items_per_page = $columns * $rows;
    $npage = Filter::getInt('npage');
    $page = Filter::getString('page');

    (!empty($_GET['search_movies'])) ? $search_movies = Filter::getUtf8('search_movies') : $search_movies = '';
    (!empty($_GET['search_shows'])) ? $search_shows = Filter::getUtf8('search_shows') : $search_shows = '';

    $page_tmdb['templates'][] = [
        'name' => 'tmdb_head',
        'tpl_file' => 'page_tmdb',
        'tpl_pri' => 100,
        'tpl_vars' => [
            'search_movies' => $search_movies,
            'search_shows' => $search_shows,
            'page' => $page,
        ]
    ];

    if (!empty($search_movies)) {
        $movies = mediadb_searchMovies(trim($search_movies));

        empty($npage) || (!empty($search_type) && $search_type !== 'movies') ? $npage_movies = 1 : $npage_movies = $npage;
        $jump_x_entrys = ($items_per_page * $npage_movies) - $items_per_page;
        $num_table_objects = count($movies);
        $movies = array_slice($movies, $jump_x_entrys, $items_per_page);

        foreach ($movies as &$movie) {
            $movie['poster'] = get_poster($movie);
        }
        if (is_array($movies) && count($movies) > 0) {

            $topt['view_type'] = 'movies_db';
            $topt['media_type'] = 'movies';
            $topt['head'] = $LNG['L_DB'];
            $topt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');

            $pager_opts['npage'] = $npage_movies;
            $pager_opts['npages'] = ceil((int) $num_table_objects / $items_per_page);
            $pager_opts['page'] = $page;
            $pager_opts['pager_place'] = 'tmdb_search_movies_container';
            $pager_opts['num_table_objs'] = $num_table_objects;
            $pager_opts['link_options'] = [
                'search_movies' => $search_movies,
                'search_shows' => $search_shows,
                'search_type' => 'movies',
            ];
            $page_tmdb['templates'][] = get_pager(array_merge($topt, $pager_opts));

            $page_tmdb['templates'][] = [
                'name' => 'tmdb_search_movies_table',
                'tpl_file' => 'items_table',
                'tpl_pri' => 10,
                'tpl_place' => 'tmdb_search_movies_container',
                'tpl_place_var' => 'items',
                'tpl_vars' => $movies,
                'tpl_common_vars' => $topt,
            ];
            $page_tmdb['templates'][] = [
                'name' => 'tmdb_search_movies_container',
                'tpl_file' => 'items_table_container',
                'tpl_pri' => 0,
                'tpl_vars' => [
                    'head' => $LNG['L_MOVIES'],
                    'items' => '',
                    'table_container_id' => 'movies'
                ]
            ];
        }
    }

    if (!empty($search_shows)) {
        $shows = mediadb_searchShows(trim($search_shows));
        empty($npage) || (!empty($search_type) && $search_type !== 'shows') ? $npage_shows = 1 : $npage_shows = $npage;
        $jump_x_entrys = ($items_per_page * $npage_shows) - $items_per_page;
        $num_table_objects = count($shows);
        $shows = array_slice($shows, $jump_x_entrys, $items_per_page);

        foreach ($shows as &$show) {
            $show['poster'] = get_poster($show);
        }
        if (is_array($shows) && count($shows) > 0) {
            $topt['view_type'] = 'shows_db';
            $topt['media_type'] = 'shows';
            $topt['head'] = $LNG['L_DB'];
            $topt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');

            $pager_opts['npage'] = $npage_shows;
            $pager_opts['npages'] = ceil((int) $num_table_objects / $items_per_page);
            $pager_opts['page'] = $page;
            $pager_opts['pager_place'] = 'tmdb_search_shows_container';
            $pager_opts['num_table_objs'] = $num_table_objects;
            $pager_opts['link_options'] = [
                'search_movies' => $search_movies,
                'search_shows' => $search_shows,
                'search_type' => 'shows',
            ];
            $page_tmdb['templates'][] = get_pager(array_merge($topt, $pager_opts));

            $page_tmdb['templates'][] = [
                'name' => 'tmdb_search_shows_table',
                'tpl_file' => 'items_table',
                'tpl_pri' => 10,
                'tpl_place' => 'tmdb_search_shows_container',
                'tpl_place_var' => 'items',
                'tpl_vars' => $shows,
                'tpl_common_vars' => $topt,
            ];
            $page_tmdb['templates'][] = [
                'name' => 'tmdb_search_shows_container',
                'tpl_file' => 'items_table_container',
                'tpl_pri' => 0,
                'tpl_vars' => [
                    'head' => $LNG['L_SHOWS'],
                    'items' => '',
                    'table_container_id' => 'shows'
                ]
            ];
        }
    }

    if (empty($_GET['search_movies']) && empty($_GET['search_shows']) && !empty($prefs->getPrefsItem('show_trending'))) {
        $topt['no_pages'] = 1;
        $results = mediadb_getTrending();
        foreach ($results['movies'] as &$result) {
            $result['poster'] = get_poster($result);
        }
        foreach ($results['shows'] as &$result) {
            $result['poster'] = get_poster($result);
        }
        if ($cfg['want_movies']) {
            $topt['view_type'] = 'movies_db';

            $topt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');

            $page_tmdb['templates'][] = [
                'name' => 'tmdb_trending_movies_table',
                'tpl_file' => 'items_table',
                'tpl_pri' => 10,
                'tpl_place' => 'tmdb_trending_movies_container',
                'tpl_place_var' => 'items',
                'tpl_vars' => $results['movies'],
                'tpl_common_vars' => $topt,
            ];
            $page_tmdb['templates'][] = [
                'name' => 'tmdb_trending_movies_container',
                'tpl_file' => 'items_table_container',
                'tpl_pri' => 0,
                'tpl_vars' => [
                    'head' => $LNG['L_TRENDING_MOVIES'],
                    'items' => [],
                ]
            ];
        }
        if ($cfg['want_shows']) {
            $topt['view_type'] = 'shows_db';

            $topt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');

            $page_tmdb['templates'][] = [
                'name' => 'tmdb_trending_shows_table',
                'tpl_file' => 'items_table',
                'tpl_pri' => 10,
                'tpl_place' => 'tmdb_trending_shows_container',
                'tpl_place_var' => 'items',
                'tpl_vars' => $results['shows'],
                'tpl_common_vars' => $topt,
            ];
            $page_tmdb['templates'][] = [
                'name' => 'tmdb_trending_shows_container',
                'tpl_file' => 'items_table_container',
                'tpl_pri' => 0,
                'tpl_vars' => [
                    'head' => $LNG['L_TRENDING_SHOWS'],
                    'items' => [],
                ]
            ];
        }
    }

    if (empty($_GET['search_movies']) && empty($_GET['search_shows']) && !empty($prefs->getPrefsItem('show_popular'))) {
        $topt['no_pages'] = 1;
        $results = mediadb_getPopular();
        foreach ($results['movies'] as &$result) {
            $result['poster'] = get_poster($result);
        }
        foreach ($results['shows'] as &$result) {
            $result['poster'] = get_poster($result);
        }

        if ($cfg['want_movies']) {
            $topt['view_type'] = 'movies_db';
            $topt['head'] = $LNG['L_POPULAR_MOVIES'];
            $topt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');

            $page_tmdb['templates'][] = [
                'name' => 'tmdb_popular_movies_table',
                'tpl_file' => 'items_table',
                'tpl_pri' => 10,
                'tpl_place' => 'tmdb_popular_movies_container',
                'tpl_place_var' => 'items',
                'tpl_vars' => $results['movies'],
                'tpl_common_vars' => $topt,
            ];
            $page_tmdb['templates'][] = [
                'name' => 'tmdb_popular_movies_container',
                'tpl_file' => 'items_table_container',
                'tpl_pri' => 0,
                'tpl_vars' => [
                    'head' => $LNG['L_POPULAR_MOVIES'],
                    'items' => [],
                ]
            ];
        }
        if ($cfg['want_shows']) {
            $topt['view_type'] = 'shows_db';
            $topt['head'] = $LNG['L_POPULAR_SHOWS'];
            $topt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');

            $page_tmdb['templates'][] = [
                'name' => 'tmdb_popular_shows_table',
                'tpl_file' => 'items_table',
                'tpl_pri' => 10,
                'tpl_place' => 'tmdb_popular_shows_container',
                'tpl_place_var' => 'items',
                'tpl_vars' => $results['shows'],
                'tpl_common_vars' => $topt,
            ];
            $page_tmdb['templates'][] = [
                'name' => 'tmdb_popular_shows_container',
                'tpl_file' => 'items_table_container',
                'tpl_pri' => 0,
                'tpl_place_var' => null,
                'tpl_place' => null,
                'tpl_vars' => [
                    'head' => $LNG['L_POPULAR_SHOWS'],
                    'items' => [],
                ]
            ];
        }
    }

    if (empty($_GET['search_movies']) && empty($_GET['search_shows']) && !empty($prefs->getPrefsItem('show_today_shows'))) {
        $topt['no_pages'] = 1;
        $results = mediadb_getTodayShows();
        $topt['view_type'] = 'shows_db';
        $topt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');

        foreach ($results['shows'] as &$result) {
            $result['poster'] = get_poster($result);
        }

        $page_tmdb['templates'][] = [
            'name' => 'tmdb_today_shows_table',
            'tpl_file' => 'items_table',
            'tpl_pri' => 10,
            'tpl_place' => 'tmdb_today_container',
            'tpl_place_var' => 'items',
            'tpl_vars' => $results['shows'],
            'tpl_common_vars' => $topt,
        ];

        $page_tmdb['templates'][] = [
            'name' => 'tmdb_today_container',
            'tpl_file' => 'items_table_container',
            'tpl_pri' => 0,
            'tpl_vars' => [
                'head' => $LNG['L_TODAY_SHOWS'],
                'items' => [],
            ]
        ];
    }
    //var_dump($page_tmdb);

    return $page_tmdb;
}

function page_torrents() {
    global $prefs, $LNG;

    $topt = [];
    $page = Filter::getString('page');
    $search_type = Filter::getString('search_type');
    $columns = $prefs->getPrefsItem('tresults_columns');
    $rows = $prefs->getPrefsItem('tresults_rows');
    $items_per_page = $columns * $rows;
    $npage = Filter::getInt('npage');

    $page_torrents['templetes'] = [];

    $media_type = null;
    $search_movies_torrents = null;
    $search_shows_torrents = null;

    if (!empty($_GET['search_movies_torrents'])) {
        $search_movies_torrents = Filter::getUtf8('search_movies_torrents');
        $media_type = 'movies';
    }

    if (!empty($_GET['search_shows_torrents'])) {
        $search_shows_torrents = Filter::getUtf8('search_shows_torrents');
        $media_type = 'shows';
    }

    $page_torrents['templates'][] = [
        'name' => 'torrents_search',
        'tpl_file' => 'page_torrents',
        'tpl_pri' => 100,
        'tpl_vars' => [
            'search_movies_word' => $search_movies_torrents,
            'search_shows_word' => $search_shows_torrents,
        ],
    ];

    if (!empty($search_type) && $search_type == 'movies') {
        $jump_x_movies_entrys = ($items_per_page * $npage) - $items_per_page;
    } else {
        $jump_x_movies_entrys = 0;
    }

    if (!empty($search_type) && $search_type == 'shows') {
        $jump_x_shows_entrys = ($items_per_page * $npage) - $items_per_page;
    } else {
        $jump_x_shows_entrys = 0;
    }

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
            $topt['media_type'] = 'movies';
            $topt['head'] = $LNG['L_TORRENT'] . ':' . $LNG['L_MOVIES'];
            $topt['num_table_objs'] = count($m_results);
            $topt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');

            $pager_opts = [];

            if ($search_type != 'movies') {
                $pager_opts['npage'] = 1;
            } else {
                $pager_opts['npage'] = $npage;
            }
            $pager_opts['page'] = $page;
            $pager_opts['link_options'] = [
                'search_movies_torrents' => $search_movies_torrents,
                'search_shows_torrents' => $search_shows_torrents,
                'search_type' => 'movies',
            ];
            $pager_opts['pager_place'] = 'torrent_search_movies_container';
            $page_torrents['templates'][] = get_pager(array_merge($topt, $pager_opts));

            $m_results = array_slice($m_results, $jump_x_movies_entrys, $items_per_page);

            $page_torrents['templates'][] = [
                'name' => 'torrent_search_movies_table',
                'tpl_file' => 'items_table',
                'tpl_pri' => 10,
                'tpl_place' => 'torrent_search_movies_container',
                'tpl_place_var' => 'items',
                'tpl_vars' => $m_results,
                'tpl_common_vars' => $topt,
            ];

            $page_torrents['templates'][] = [
                'name' => 'torrent_search_movies_container',
                'tpl_file' => 'items_table_container',
                'tpl_pri' => 0,
                'tpl_vars' => [
                    'head' => $LNG['L_MOVIES'],
                    'items' => [],
                ]
            ];
        } else {
            $page_torrents['templates'][] = [
                'name' => 'msgbox',
                'tpl_file' => 'msgbox',
                'tpl_pri' => 4,
                'tpl_vars' => [
                    'title' => $LNG['L_TORRENT'] . ' ' . $LNG['L_MOVIES'],
                    'body' => $LNG['L_NOTHING_FOUND'],
                ]
            ];
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
            $topt['media_type'] = 'shows';
            $topt['num_table_objs'] = count($m_results);
            $topt['head'] = $LNG['L_TORRENT'] . ':' . $LNG['L_SHOWS'];
            $topt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');
            $m_results = array_slice($m_results, $jump_x_shows_entrys, $items_per_page);

            $pager_opts = [];

            if ($search_type != 'shows') {
                $pager_opts['npage'] = 1;
            } else {
                $pager_opts['npage'] = $npage;
            }
            $pager_opts['page'] = $page;
            $pager_opts['link_options'] = [
                'search_movies_torrents' => $search_movies_torrents,
                'search_shows_torrents' => $search_shows_torrents,
                'search_type' => 'shows',
            ];
            $pager_opts['pager_place'] = 'torrent_search_shows_container';
            $page_torrents['templates'][] = get_pager(array_merge($topt, $pager_opts));

            $page_torrents['templates'][] = [
                'name' => 'torrent_search_shows_table',
                'tpl_file' => 'items_table',
                'tpl_pri' => 10,
                'tpl_place' => 'torrent_search_shows_container',
                'tpl_place_var' => 'items',
                'tpl_vars' => $m_results,
                'tpl_common_vars' => $topt,
            ];

            $page_torrents['templates'][] = [
                'name' => 'torrent_search_shows_container',
                'tpl_file' => 'items_table_container',
                'tpl_pri' => 0,
                'tpl_vars' => [
                    'head' => $LNG['L_SHOWS'],
                    'items' => [],
                ]
            ];
        } else {
            $page_torrents['templates'][] = [
                'name' => 'msgbox',
                'tpl_file' => 'msgbox',
                'tpl_pri' => 4,
                'tpl_vars' => [
                    'title' => $LNG['L_TORRENT'] . ' ' . $LNG['L_SHOWS'],
                    'body' => $LNG['L_NOTHING_FOUND'],
                ]
            ];
        }
    }

    if (empty($search_movies_torrents) && empty($search_shows_torrents)) {
        if ($prefs->getPrefsItem('movies_cached')) {
            $topt = [];
            $topt['view_type'] = 'movies_torrent';
            $topt['search_type'] = 'movies';
            $topt['media_type'] = 'movies';
            $topt['head'] = $LNG['L_MOVIES'];
            $topt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');
            $topt['table_container_id'] = 'movies';
            $movies_cache = show_cached_torrents($topt);
            $page_torrents['templates'] = array_merge($page_torrents['templates'], $movies_cache['templates']);
        }

        if ($prefs->getPrefsItem('shows_cached')) {
            $topt = [];
            $topt['view_type'] = 'shows_torrent';
            $topt['search_type'] = 'shows';
            $topt['media_type'] = 'shows';
            $topt['head'] = $LNG['L_SHOWS'];
            $topt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');
            $topt['table_container_id'] = 'shows';

            $shows_cache = show_cached_torrents($topt);
            $page_torrents['templates'] = array_merge($page_torrents['templates'], $shows_cache['templates']);
        }
    }

    return $page_torrents;
}

function page_wanted() {
    global $db, $cfg, $trans;

    $wanted['templates'] = [];

    $wanted_tpl_list = [];
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
    $wanted_tpl_list = wanted_list();
    $quality_tags = $ignore_tags = $require_tags = $require_or_tags = [];

    if (!empty($cfg['torrent_quality_prefs'])) {
        $quality_tags = $cfg['torrent_quality_prefs'];
    }

    if (!empty($cfg['torrent_ignore_prefs'])) {
        $ignore_tags = $cfg['torrent_ignore_prefs'];
    }

    if (!empty($cfg['torrent_require_prefs'])) {
        $require_tags = $cfg['torrent_require_prefs'];
    }

    if (!empty($cfg['torrent_require_or_prefs'])) {
        $require_or_tags = $cfg['torrent_require_or_prefs'];
    }

    $wanted['templates'][] = [
        'name' => 'wanted',
        'tpl_file' => 'wanted',
        'tpl_pri' => 0,
        'tpl_place_var' => null,
        'tpl_place' => null,
        'tpl_vars' => [
            'key' => 'value', //First can't be array for avoid the frontend:58 loop
            'quality_tags' => $quality_tags,
            'ignore_tags' => $ignore_tags,
            'require_tags' => $require_tags,
            'require_or_tags' => $require_or_tags,
            'wanted_list' => [],
            'track_show_list' => []]
    ];

    $wanted['templates'] = array_merge($wanted['templates'], $wanted_tpl_list['templates']);

    return $wanted;
}

function page_identify() {
    global $db, $cfg, $LNG;

    $identify['templates'] = [];

    $media_type = Filter::getString('media_type');
    $id = Filter::getInt('identify');
    $id_all = Filter::getInt('identify_all');

    if ($media_type === false || ($id === false && $id_all === false)) {
        return ['msg_type' => 1, 'title' => 'L_ERROR', 'body' => '1A1002'];
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
     * Rename
     */
    if (isset($_POST['rename_file_btn'])) {
        $rename_error = false;
        $new_file_name = trim(Filter::postFilename('rename_file'));
        $rename_update = [];

        if (!empty($new_file_name) && ($new_file_name !== $ident_item['file_name'])) {
            $rename_update = ['file_name' => $new_file_name];
            if ($media_type == 'shows') {
                $SE = getFileEpisode($new_file_name);
                if (valid_array($SE)) {
                    $rename_update['season'] = (int) $SE['season'];
                    $rename_update['episode'] = (int) $SE['episode'];
                } else {
                    $rename_error = true;
                }
            }

            $new_name_path = dirname($ident_item['path']) . '/' . $new_file_name;
            if (!$rename_error) {
                rename($ident_item['path'], $new_name_path);
                if (file_exists($new_name_path)) {
                    $rename_update['path'] = $new_name_path;
                    $db->update('library_' . $media_type, $rename_update, ['id' => ['value' => $ident_item['id']]]);
                    $ident_item['file_name'] = $new_file_name;
                    $ident_item['path'] = $new_name_path;
                }
            }
        }
    }

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
        return ['msg_type' => 1, 'title' => 'L_SUCCESS', 'body' => 'L_ADDED_SUCCESSFUL'];
    }
    !empty($_POST['submit_title']) ? $submit_title = Filter::postUtf8('submit_title') : $submit_title = getFileTitle(basename($ident_item['path']));

    $tdata['search_title'] = $submit_title;

    $item_selected = [];

    if (!empty($submit_title)) {
        ($media_type == 'movies') ? $db_media = mediadb_searchMovies($submit_title) : $db_media = mediadb_searchShows($submit_title);
        if (valid_array($db_media)) {

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
            $tdata['media_results'] = [
                'id' => $id,
                'selected' => $conf_selected,
                'items' => $values
            ];
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

    $identify_vars = array_merge($ident_item, $tdata);
    $identify_vars['head'] = $topt['head'] = strtoupper($LNG['L_IDENTIFY']);

    if (is_writable($identify_vars['path'])) {
        $identify_vars['is_writable'] = 1;
    }
    if (is_link($identify_vars['path'])) {
        $identify_vars['is_link'] = 1;
    }


    $identify['templates'][] = [
        'name' => 'identify',
        'tpl_file' => 'identify_adv',
        'tpl_pri' => 4,
        'tpl_place_var' => null,
        'tpl_place' => null,
        'tpl_vars' => $identify_vars
    ];

    return $identify;
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
    global $trans;

    $transmission['templates'] = [];

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

        $transmission['templates'][] = [
            'name' => 'transmissio-row',
            'tpl_file' => 'transmission-row',
            'tpl_pri' => 10,
            'tpl_place' => 'transmission-body',
            'tpl_place_var' => 'body',
            'tpl_vars' => array_merge($transfer, $tdata)
        ];
    }

    $transmission['templates'][] = [
        'name' => 'transmission-body',
        'tpl_file' => 'transmission-body',
        'tpl_pri' => 0,
        'tpl_place' => null,
        'tpl_place_var' => null,
        'tpl_vars' => ['body' => '']
    ];

    return $transmission;
}

function page_config() {
    global $config, $LNG;

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

    $main = [];

    $admin_config_tpl = [
        'name' => 'admin_config',
        'tpl_file' => 'admin_config',
        'tpl_pri' => 10,
        'tpl_vars' => [],
    ];

    $tpl_vars = [];

    $tpl_vars['title'] = '';
    $tpl_vars['selected_category'] = Filter::getString('category');
    if (empty($tpl_vars['selected_category'])) {
        $tpl_vars['selected_category'] = 'L_MAIN';
    }

    $config_data = $config->getConfig();

    $i = 1;
    $imax = count($config_data);

    $tpl_vars['categories'] = [];
    $tpl_vars['cfg'] = [];

    $categories = [];
    foreach ($config_data as $cfg) {
        ($i === 1) ? $tpl_vars['first'] = 1 : null;
        ($i === $imax) ? $tpl_vars['last'] = 1 : null;

        if (!empty($cfg['category']) && $cfg['category'] != 'L_PRIV') {
            if (!in_array($cfg['category'], $categories)) {
                $categories[] = $cfg['category'];
                isset($LNG[$cfg['category']]) ? $cat_display = ucfirst($LNG[$cfg['category']]) : $cat_display = $cfg['category'];
                $category['cat_raw'] = $cfg['category'];
                $category['cat_display'] = $cat_display;
                $tpl_vars['categories'][] = $category;
            }

            if ($cfg['public'] != 1 || $cfg['category'] != $tpl_vars['selected_category']) {
                //not add then -- imax
                $imax--;
                continue;
            }
            $cfg['category'] = $LNG[$cfg['category']];
            if (!empty($cfg['cfg_desc']) && isset($LNG[$cfg['cfg_desc']])) {
                $cfg['cfg_desc'] = $LNG[$cfg['cfg_desc']];
            }
            if ($cfg['type'] == 3) {
                if ($cfg['cfg_value'] == 0) {
                    $cfg['selected_yes'] = '';
                    $cfg['selected_no'] = ' selected ';
                } else {
                    $cfg['selected_yes'] = ' selected ';
                    $cfg['selected_no'] = '';
                }
            }
            if ($cfg['type'] == 8) {
                $cfg['cfg_value_array'] = $config->commaToArray($cfg['cfg_value']);
            }

            $tpl_vars['cfg'][] = $cfg;
        }

        $i++;
    }

    $admin_config_tpl['tpl_vars'] = $tpl_vars;

    $main['templates'][] = $admin_config_tpl;

    return $main;
}

function page_login() {
    global $cfg, $user;

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
                    header("Location: {$cfg['REL_PATH']}/?page= ");
                    exit();
                }
            }
        }
    }

    $main = [];
    $main['templates'] = [];

    $users_db = $user->getProfiles();

    $profile_tpl = [
        'name' => 'profile_box',
        'tpl_file' => 'profile_box',
        'tpl_pri' => 9,
        'tpl_place' => 'login',
        'tpl_place_var' => 'profiles',
        'tpl_vars' => [],
    ];

    foreach ($users_db as $db_user) {
        if ($db_user['disable'] != 1 && $db_user['hide_login'] != 1) {
            $profile_tpl['tpl_vars']['username'] = $db_user['username'];
            $main['templates'][] = $profile_tpl;
        }
    }

    $login_tpl = [
        'name' => 'login',
        'tpl_file' => 'login',
        'tpl_pri' => 0,
        'tpl_vars' => [],
    ];
    $main['templates'][] = $login_tpl;

    return $main;
}

function page_logout() {
    global $cfg;

    $_SESSION['uid'] = 0;
    ($_COOKIE) ? setcookie("uid", null, -1) : null;
    ($_COOKIE) ? setcookie("sid", null, -1) : null;
    session_regenerate_id();
    session_destroy();
    header("Location: {$cfg['REL_PATH']}/?page=login ");
    exit(0);
}

//TODO: error msg
function page_localplayer() {
    global $db;

    $id = Filter::getInt('id'); //file id
    $mid = Filter::getInt('mid'); //master id
    $media_type = Filter::getString('media_type');

    if ((empty($id) && empty($mid)) || empty($media_type)) {
        //['title' => 'L_ERROR', 'body' => '1A1003'];
        return false;
    }
    $library = 'library_' . $media_type;
    $library_master = 'library_master_' . $media_type;

    if (!empty($id)) {

        $item = $db->getItemById($library, $id);
        if (!valid_array($item)) {
            //['title' => 'L_ERROR', 'body' => 'L_ERR_ITEM_NOT_FOUND'];
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
            //['title' => 'L_ERROR', 'body' => 'L_ERR_ITEM_NOT_FOUND'];
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
