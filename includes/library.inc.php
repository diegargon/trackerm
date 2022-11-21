<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function show_my_media($media_type) {
    global $db, $prefs, $LNG;

    $page_library = [];

    $templates = [];
    $templates['templates'] = [];

    $npage = Filter::getInt('npage');
    $search_type = Filter::getString('search_type');
    $library = 'library_' . $media_type;
    $library_master = 'library_master_' . $media_type;
    $page = Filter::getString('page');
    empty($npage) || (!empty($search_type) && $search_type !== $media_type) ? $npage = 1 : null;
    $topt['npage'] = $npage;

    if (!empty($_POST['search_keyword'])) {
        $search_keyword = Filter::postString('search_keyword');
    } else if (!empty($_GET['search_keyword'])) {
        $search_keyword = Filter::getString('search_keyword');
    }
    !empty($search_keyword) ? $topt['search_keyword'] = $search_keyword : null;

    if ($media_type == 'movies') {
        if (!empty($_POST['mult_movies_select'])) {
            ident_by_idpairs('movies', $_POST['mult_movies_select']);
        }
    } else {
        if (isset($_POST['mult_shows_select']) && !empty($_POST['mult_shows_select'])) {
            ident_by_idpairs('shows', $_POST['mult_shows_select']);
        }
    }
    if (!empty(($ident_delete = Filter::getInt('ident_delete'))) && ($_GET['media_type'] == $media_type)) {
        $db->deleteItemById($library, $ident_delete);
    }

    //FIXME
    $identify_media = show_identify_media($media_type);
    if (!empty($identify_media) && isset($identify_media['templates'])) {
        $templates['templates'] = array_merge($templates['templates'], $identify_media['templates']);
    }

    $rows = $prefs->getPrefsItem('tresults_rows');
    $columns = $prefs->getPrefsItem('tresults_columns');

    $n_results = $rows * $columns;
    $npage == 1 ? $start = 0 : $start = ($npage - 1) * $n_results;

    $views_items = get_view_data($media_type);

    /* Build a list of all master ids with all files "seen". We need for ignore on view mode */
    if ($prefs->getPrefsItem('view_mode') && valid_array($views_items)) {
        $ignore_master_ids = '';
        foreach ($views_items as $key_view_item => $view_item) {
            if (($view_item['total_items'] - $view_item['view_items']) <= 0) {
                empty($ignore_master_ids) ? $ignore_master_ids .= $key_view_item : $ignore_master_ids .= ',' . $key_view_item;
            }
        }
    }
    //FIN VIEW_ITEMS
    $where = '';
    if (!empty($search_keyword)) {
        $where = " WHERE title LIKE \"%$search_keyword%\" ";
    }
    if ($prefs->getPrefsItem('view_mode') && !empty($ignore_master_ids)) {
        if (empty($where)) {
            $where .= " WHERE id NOT IN ($ignore_master_ids) ";
        } else {
            $where .= " AND id NOT IN ($ignore_master_ids) ";
        }
    }
    $topt['num_table_objs'] = $db->qSingle("SELECT COUNT(*)  FROM $library_master $where");
    $query = "SELECT * FROM $library_master $where ORDER BY items_updated DESC LIMIT $start,$n_results ";

    $results = $db->query($query);
    $media = $db->fetchAll($results);

    //Rest view_item to total_items for number of unseen items
    if (valid_array($views_items)) {
        foreach ($media as $kmedia => $vmedia) {
            foreach ($views_items as $kview_item => $vview_item) {
                if ($kview_item == $vmedia['id']) {
                    $media[$kmedia]['total_unseen_items'] = $vmedia['total_items'] - $vview_item['view_items'];
                    break;
                }
            }
        }
    }

    foreach ($media as $kmedia => $vmedia) {
        //TODO: round old reg remove in the future
        $media[$kmedia]['rating'] = round($vmedia['rating'], 1);
        $media[$kmedia]['popularity'] = round($vmedia['popularity'], 1);
        //
        $media[$kmedia]['poster'] = get_poster($vmedia);
        !empty($vmedia['release']) ? $media[$kmedia]['title'] = $vmedia['title'] . ' (' . strftime("%Y", strtotime($vmedia['release'])) . ')' : null;
        empty($vmedia['trailer']) && !empty($vmedia['guessed_trailer']) && $vmedia['guessed_trailer'] != -1 ? $vmedia['trailer'] = $vmedia['guessed_trailer'] : null;
        !empty($vmedia['size']) ? $media[$kmedia]['size'] = human_filesize($vmedia['size']) : null;
        !empty($vmedia['total_size']) ? $media[$kmedia]['total_size'] = human_filesize($vmedia['total_size']) : null;
    }

    $topt['view_type'] = $media_type . '_library';
    if (valid_array($media)) {
        $topt['media_type'] = $media_type;
        $topt['page'] = $page;
        $pager_opts['npage'] = $npage;
        $pager_opts['pager_place'] = $media_type . '_library';
        $pager_opts['link_options'] = ['search_type' => $media_type];
        $templates['templates'][] = get_pager(array_merge($topt, $pager_opts));
        $page_library = [
            'name' => 'items_library_' . $media_type,
            'tpl_file' => 'items_table',
            'tpl_pri' => 5,
            'tpl_place' => $media_type . '_library',
            'tpl_place_var' => 'items',
        ];

        $page_library['tpl_vars'] = $media;
        $topt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');
        $page_library['tpl_common_vars'] = $topt;

        $templates['templates'][] = $page_library;
    } else {

        $templates['templates'][] = [
            'name' => 'msgbox',
            'tpl_file' => 'msgbox',
            'tpl_pri' => 4,
            'tpl_place' => $media_type . '_library',
            'tpl_place_var' => 'items',
            'tpl_vars' => [
                'title' => $LNG['L_' . strtoupper($media_type)],
                'body' => $LNG['L_NO_RESULTS'],
            ]
        ];
    }


    //var_dump($templates);
    return $templates;
}

function get_pager(array $opts) {
    global $prefs;

    $columns = (int) $prefs->getPrefsItem('tresults_columns');
    $rows = (int) $prefs->getPrefsItem('tresults_rows');
    isset($opts['search_keyword']) ? $search_keyword = $opts['search_keyword'] : $search_keyword = $opts['search_keyword'] = '';
    isset($opts['search_movies']) ? $search_movies = $opts['search_movies'] : $search_movies = $opts['search_movies'] = '';
    isset($opts['search_shows']) ? $search_shows = $opts['search_shows'] : $search_shows = $opts['search_shows'] = '';
    isset($opts['search_movies_torrents']) ? $search_movies_torrents = $opts['search_movies_torrents'] : $search_movies_torrents = $opts['search_movies_torrents'] = '';
    isset($opts['search_shows_torrents']) ? $search_shows_torrents = $opts['search_shows_torrents'] : $search_shows_torrents = $opts['search_shows_torrents'] = '';

    $items_per_page = $columns * $rows;
    $num_pages = ceil((int) $opts['num_table_objs'] / $items_per_page);
    if ($opts['npage'] > 1) {
        $page_previous = $opts['npage'] - 1;
    } else {
        $page_previous = 1;
    }

    if ($opts['npage'] < $num_pages) {
        $page_next = $opts['npage'] + 1;
    } else {
        $page_next = $opts['npage'];
    }

    if (isset($opts['tpl_pri'])) {
        $tpl_pri = $opts['tpl_pri'];
    } else {
        $tpl_pri = 100;
    }

    $link_options = '';
    if (!empty($opts['link_options']) && is_array($opts['link_options'])) {
        foreach ($opts['link_options'] as $key => $value) {
            $link_options .= '&' . $key . '=' . $value;
        }
    }

    $pager = [
        'name' => 'pager_' . $opts['media_type'],
        'tpl_file' => 'pager',
        'tpl_pri' => $tpl_pri,
        'tpl_place' => $opts['pager_place'],
        'tpl_place_var' => 'pager',
        'tpl_vars' => [
            'media_type' => $opts['media_type'],
            'page' => $opts['page'],
            'page_previous' => $page_previous,
            'npage' => (int) $opts['npage'],
            'page_next' => $page_next,
            'nitems' => (int) $opts['num_table_objs'],
            'npages' => $num_pages,
            'nitems_per_page' => $items_per_page,
            'search_keyword' => $search_keyword,
            'search_movies' => $search_movies,
            'search_shows' => $search_shows,
            'search_movies_torrents' => $search_movies_torrents,
            'search_shows_torrents' => $search_shows_torrents,
            'link_options' => $link_options,
        ],
    ];

    return $pager;
}

function get_poster($item) {
    global $cfg;

    $poster = $cfg['img_url'] . '/not_available.jpg';

    if ($cfg['cache_images']) {
        if (!empty($item['custom_poster'])) {
            $cache_img_response = cache_img($item['custom_poster']);
            if ($cache_img_response !== false) {
                $poster = $cache_img_response;
            } else {
                $poster = $item['custom_poster'];
            }
        } else if (!empty($item['poster'])) {
            $cache_img_response = cache_img($item['poster']);
            if ($cache_img_response !== false) {
                $poster = $cache_img_response;
            } else {
                if (Filter::varImgUrl($item['poster'])) {
                    $poster = $item['poster'];
                }
            }
        } else if (!empty($item['guessed_poster']) && $item['guessed_poster'] != -1) {
            $cache_img_response = cache_img($item['guessed_poster']);
            if ($cache_img_response !== false) {
                $poster = $cache_img_response;
            } else {
                $poster = $item['guessed_poster'];
            }
        }
    } else {
        if (!empty($item['custom_poster'])) {
            $poster = $item['custom_poster'];
        } else if (!empty($item['poster'])) {
            $poster = $item['poster'];
        } else if (!empty($item['guessed_poster']) && $item['guessed_poster'] != -1) {
            $poster = $item['guessed_poster'];
        }
    }

    return $poster;
}

/* Format genres to view */

function get_fgenres(string $media_type, array $item) {
    global $cfg, $LNG;

    $fgenres = [];
    if (empty($item['genres'])) {
        return $fgenres;
    }

    $genres = $item['genres'];
    $genres = str_replace('][', ',', $genres);
    $genres = str_replace(['[', ']'], '', $genres);

    $genres_ary = explode(',', $genres);
    foreach ($genres_ary as $vgenre) {
        if (!empty($cfg['TMDB_GENRES'][$vgenre])) {
            $lang_genre = $LNG[$cfg['TMDB_GENRES'][$vgenre]];
        } else {
            $lang_genre = $vgenre;
        }
        $fgenres[] = [
            'name' => $lang_genre,
            'id' => $vgenre,
        ];
    }

    return $fgenres;
}

/* Format TMDB Collection */

function get_fcollection(string $media_type, array $item) {
    global $db;

    $fcollection = [];

    $results = $db->query("SELECT id,title FROM groups WHERE media_type = '$media_type' AND type = 3 AND type_id = '{$item['collection']}' LIMIT 1");
    $collection = $db->fetch($results);
    $db->free($results);

    if (!valid_array($collection)) {
        return $item['collection'];
    }

    $fcollection[] = [
        'name' => $collection['title'],
        'id' => $collection['id'],
        'group_type' => 3,
        'view_name' => 'view_group'
    ];
    return $fcollection;
}

/* Format names collections to view */

function get_fnames(string $col_type, string $media_type, array $item) {
    $fnames = [];

    if (empty($item[$col_type])) {
        return false;
    }

    $names = explode(',', $item[$col_type]);

    $view_name = '';

    if ($col_type == 'director') {
        $view_name = 'view_director';
    } else if ($col_type == 'cast') {
        $view_name = 'view_cast';
    } else if ($col_type == 'writer') {
        $view_name = 'view_writer';
    } else {
        return false;
    }

    foreach ($names as $name) {
        $fnames[] = [
            'name' => $name,
            'category' => $col_type,
            'view_name' => $view_name
        ];
    }

    return $fnames;
}

function mark_masters_views($media_type, &$masters) {
    global $db, $user;

    $library = 'library_' . $media_type;

    $where_view_data = [
        'uid' => ['value' => $user->getId()],
        'media_type' => ['value' => $media_type],
    ];
    $results = $db->select('view_media', '*', $where_view_data);
    $views_data = $db->fetchAll($results);
    $views_tmdb_ids = [];
    $views_hashs = [];
    /*
     * Get from view_media the masters ids for get total_items
     * Get from view_media the file_hashes for get total item view
     */
    foreach ($views_data as $view_data) {
        $views_tmdb_ids[] = $view_data['themoviedb_id'];
        $views_hashs[] = $view_data['file_hash'];
    }

    /*  view_item[master_id][total_items] && view_item[master_id][view items]  */
    $views_items = [];
    /* Get Master Ids for query library files */
    $master_ids = [];
    foreach ($masters as $master) {
        $views_items[$master['id']]['total_items'] = $master['total_items'];
        $master_ids[] = $master['id'];
    }
    /* Get Files of master ids */
    $view_files_matchs = $db->selectMultiple($library, 'master', $master_ids, 'id,master,file_hash');
    foreach ($view_files_matchs as $vfm) {
        empty($views_items[$vfm['master']]['view_items']) ? $views_items[$vfm['master']]['view_items'] = 0 : null;
        foreach ($views_data as $view_data) {
            /* if we have a  file hashes coincidence we see that file */
            if ($view_data['file_hash'] == $vfm['file_hash']) {
                $views_items[$vfm['master']]['view_items']++;
            }
        }
    }

    //Rest view_item count to total_items
    if (valid_array($views_items)) {
        foreach ($masters as $kmaster => $vmaster) {
            foreach ($views_items as $kview_item => $vview_item) {
                if ($kview_item == $vmaster['id']) {
                    $masters[$kmaster]['total_unseen_items'] = $vmaster['total_items'] - $vview_item['view_items'];
                    break;
                }
            }
        }
    }
}

function show_collections() {
    global $db, $prefs;

    $page_collection['templates'] = [];

    $media_type = 'movies';
    $npage = Filter::getInt('npage');
    $search_type = Filter::getString('search_type');
    $page = Filter::getString('page');
    empty($npage) || (!empty($search_type) && $search_type !== $media_type) ? $npage = 1 : null;

    $opt = [];

    $rows = $prefs->getPrefsItem('tresults_rows');
    $columns = $prefs->getPrefsItem('tresults_columns');

    $n_results = $rows * $columns;
    $npage == 1 ? $start = 0 : $start = ($npage - 1) * $n_results;

    /* Need total of collections for pager, and id+elements of each collection */
    $query = "SELECT id,collection, COUNT(*) as total FROM library_master_movies WHERE collection IS NOT NULL GROUP BY collection HAVING count(*) > 1 ";
    $results = $db->query($query);

    $item_counts = $db->fetchAll($results);
    $total_collections = count($item_counts);

    $opt['total_collections'] = $total_collections;

    /* Get all register  with same collection id (get one of each) having more than two coincidences */
    $query = "SELECT DISTINCT collection  FROM library_master_movies WHERE collection IS NOT NULL AND collection IN (SELECT collection FROM library_master_movies GROUP BY collection HAVING count(*) > 1 ) ORDER BY title";
    $results = $db->query($query);
    $collections_files = $db->fetchAll($results);

    if (!valid_array($collections_files)) {
        return false;
    }

    $collect_ids = '';
    foreach ($collections_files as $collection_file) {
        empty($collect_ids) ? $collect_ids = $collection_file['collection'] : $collect_ids .= ',' . $collection_file['collection'];
    }

    $query = "SELECT * FROM groups WHERE media_type = 'movies' AND type = '3' AND type_id IN ($collect_ids) LIMIT $start,$n_results";
    $results = $db->query($query);
    $collections = $db->fetchAll($results);

    //fill total_items
    foreach ($collections as $kcol => $vcol) {
        foreach ($item_counts as $item_count) {
            if ($item_count['collection'] == $vcol['type_id']) {
                $collections[$kcol]['total_items'] = $item_count['total'];
                break;
            }
        }
    }

    if (valid_array($collections)) {

        $topt['view_type'] = $media_type . '_collection';
        $topt['group_type'] = 3;
        $topt['media_type'] = $media_type;
        $topt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');

        $pager_opts['npage'] = $npage;
        $pager_opts['page'] = $page;
        $pager_opts['pager_place'] = $media_type . '_library';
        $pager_opts['num_table_objs'] = $total_collections;
        $pager_opts['link_options'] = ['search_type' => $media_type];

        $page_collection['templates'][] = get_pager(array_merge($topt, $pager_opts));

        $page_collection['templates'][] = [
            'name' => 'items_collection_' . $media_type,
            //'tpl_file' => 'collection_item',
            'tpl_file' => 'items_table',
            'tpl_pri' => 5,
            'tpl_place' => $media_type . '_library',
            'tpl_place_var' => 'items',
            'tpl_vars' => $collections,
            'tpl_common_vars' => $topt,
        ];
    }


    return $page_collection;
}

function show_genres(string $media_type) {
    global $cfg, $LNG, $prefs;

    $npage = Filter::getInt('npage');
    $search_type = Filter::getString('search_type');
    $page = Filter::getString('page');
    $items = [];
    empty($npage) || (!empty($search_type) && $search_type !== $media_type) ? $npage = 1 : null;

    $page_genres['templates'] = [];

    $rows = $prefs->getPrefsItem('tresults_rows');
    $columns = $prefs->getPrefsItem('tresults_columns');

    $n_results = $rows * $columns;
    $npage == 1 ? $start = 0 : $start = ($npage - 1) * $n_results;

    $genres = $cfg['tmdb_genres_' . $media_type];

    $i = 0;
    $added_items = 0;
    foreach ($genres as $kgenre => $vgenre) {

        if ($i >= $start && $added_items < $n_results) {
            $items[] = [
                'id' => $kgenre,
                'title' => $LNG[$vgenre],
            ];
            $added_items++;
        }
        $i++;
    }

    $topt['media_type'] = $media_type;
    $topt['get_params'] = ['page' => 'view_genres', 'media_type' => $media_type];
    $topt['view_type'] = $media_type . '_library';
    $topt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');
    $topt['num_table_objs'] = count($genres);

    $pager_opts['npage'] = $npage;
    $pager_opts['page'] = $page;
    $pager_opts['pager_place'] = $media_type . '_library';
    $pager_opts['link_options'] = ['search_type' => $media_type];

    $page_genres['templates'][] = get_pager(array_merge($topt, $pager_opts));

    $page_genres['templates'][] = [
        'name' => 'genres_' . $media_type,
        'tpl_file' => 'genre_item',
        'tpl_pri' => 5,
        'tpl_place' => $media_type . '_library',
        'tpl_place_var' => 'items',
        'tpl_vars' => $items,
        'tpl_common_vars' => $topt,
    ];

    return $page_genres;
}

function show_directors(string $media_type) {
    return view_media_name($media_type, 'director');
}

function show_cast(string $media_type) {
    return view_media_name($media_type, 'cast');
}

function show_writer(string $media_type) {
    return view_media_name($media_type, 'writer');
}

function view_media_name(string $media_type, string $name_type) {
    global $prefs, $db;

    $npage = Filter::getInt('npage');
    $search_type = Filter::getString('search_type');
    $page = Filter::getString('page');
    $items = [];
    empty($npage) || (!empty($search_type) && $search_type !== $media_type) ? $npage = 1 : null;

    $rows = $prefs->getPrefsItem('tresults_rows');
    $columns = $prefs->getPrefsItem('tresults_columns');

    $n_results = $rows * $columns;
    $npage == 1 ? $start = 0 : $start = ($npage - 1) * $n_results;

    $page_names['templates'] = [];

    $library_master = 'library_master_' . $media_type;
    $field_name = $name_type;

    $results = $db->select($library_master, $field_name);
    $items = $db->fetchAll($results);

    $names = [];
    foreach ($items as $item) {
        if (empty($item[$field_name])) {
            continue;
        }

        $names = array_merge($names, array_map('trim', explode(',', $item[$field_name])));
    }
    $dups_names = [];
    foreach (array_count_values($names) as $val => $c) {
        ($c > 1) ? $dups_names[] = $val : null;
    }

    $i = 0;
    $added_items = 0;
    $show_names = [];
    foreach ($dups_names as $vname) {

        if ($i >= $start && $added_items < $n_results) {
            $show_names[] = [
                'name' => $vname,
            ];
            $added_items++;
        }
        $i++;
    }

    if ($name_type == 'director') {
        $view_page = 'view_director';
    } else if ($name_type == 'cast') {
        $view_page = 'view_cast';
    } else if ($name_type == 'writer') {
        $view_page = 'view_writer';
    } else {
        return false;
    }

    $pager_opt['npage'] = $npage;
    $pager_opt['nitems'] = count($dups_names);
    $pager_opt['pager_place'] = $media_type . '_library';
    $pager_opt['page'] = $page;
    $pager_opt['link_options'] = ['search_type' => $media_type];

    $build_opt['media_type'] = $media_type;
    $build_opt['item_tpl'] = 'collector_name';
    $build_opt['num_table_objs'] = count($dups_names);
    $build_opt['tpl_items_break'] = $prefs->getPrefsItem('tresults_columns');
    $build_opt['view_page'] = $view_page;

    $page_names['templates'][] = get_pager(array_merge($build_opt, $pager_opt));

    $page_names['templates'][] = [
        'name' => 'names_' . $media_type,
        'tpl_file' => 'collection_name',
        'tpl_pri' => 5,
        'tpl_place' => $media_type . '_library',
        'tpl_place_var' => 'items',
        'tpl_vars' => $show_names,
        'tpl_common_vars' => $build_opt,
    ];

    return $page_names;
}

function get_view_data($media_type) {
    global $user, $db;

    $library = 'library_' . $media_type;
    $library_master = 'library_master_' . $media_type;
    $where_view_data = [
        'uid' => ['value' => $user->getId()],
        'media_type' => ['value' => $media_type],
    ];
    $results = $db->select('view_media', '*', $where_view_data);
    $views_data = $db->fetchAll($results);
    $views_tmdb_ids = [];
    $views_hashs = [];
    /*
     * Get from view_media the masters ids for get total_items
     * Get from view_media the file_hashes for get total item view
     */
    foreach ($views_data as $view_data) {
        $views_tmdb_ids[] = $view_data['themoviedb_id'];
        $views_hashs[] = $view_data['file_hash'];
    }

    $views_tmdb_ids_uniq = array_unique($views_tmdb_ids);
    //Get Masters for fill total_items
    $view_master_matchs = $db->selectMultiple($library_master, 'themoviedb_id', $views_tmdb_ids_uniq, 'id,total_items');
    /*  view_item[master_id][number total items]  and view_item[master_id][number view items] */
    $views_items = [];
    /* Get Master Ids for query library files */
    $master_ids = [];
    foreach ($view_master_matchs as $vmm) {
        $views_items[$vmm['id']]['total_items'] = $vmm['total_items'];
        $views_items[$vmm['id']]['view_items'] = 0;
        $master_ids[] = $vmm['id'];
    }
    /* Get Files of master ids */
    $view_files_matchs = $db->selectMultiple($library, 'master', $master_ids, 'id,master,file_hash');

    foreach ($view_files_matchs as $vfm) {
        $views_items[$vfm['master']]['master'] = $vfm['master'];
        foreach ($views_data as $view_data) {
            /* if we have a  file hashes coincidence we see that file */
            if ($view_data['file_hash'] == $vfm['file_hash']) {
                $views_items[$vfm['master']]['view_items']++;
            }
        }
    }

    return $views_items;
}
