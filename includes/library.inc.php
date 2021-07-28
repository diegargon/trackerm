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
    global $db, $prefs, $user, $frontend;

    $page_library = '';
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
    $page_library .= show_identify_media($media_type);

    $rows = $prefs->getPrefsItem('tresults_rows');
    $columns = $prefs->getPrefsItem('tresults_columns');

    $n_results = $rows * $columns;
    $npage == 1 ? $start = 0 : $start = ($npage - 1) * $n_results;

    $views_items = get_view_data($media_type);

    /* Build a list of all master ids with all files "seen". We need for ignore on view mode */
    if ($prefs->getPrefsItem('view_mode')) {
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
    $topt['num_table_rows'] = $db->qSingle("SELECT COUNT(*)  FROM $library_master $where");
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

    $topt['view_type'] = $media_type . '_library';
    if (valid_array($media)) {
        $topt['search_type'] = $media_type;
        $topt['media_type'] = $media_type;
        $topt['page'] = $page;
        $page_library .= buildTable('L_' . strtoupper($media_type), $media, $topt);
    } else {
        $page_library .= $frontend->msgBox(['title' => 'L_' . strtoupper($media_type), 'body' => 'L_NO_RESULTS']);
    }

    return $page_library;
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
                $poster = $item['poster'];
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

function get_fgenres($item) {
    global $cfg, $LNG;

    $genres = '';
    if (empty($item['genre'])) {
        return $genres;
    }

    $genres_ary = explode(',', $item['genre']);
    foreach ($genres_ary as $vgenre) {
        if (!empty($cfg['TMDB_GENRES'][$vgenre])) {
            $lang_genre = $LNG[$cfg['TMDB_GENRES'][$vgenre]];
        } else {
            $lang_genre = $LNG['L_CAT_NOTEXISTS'];
        }
        $genres .= Html::span(['class' => 'fgenres'], $lang_genre);
    }

    return $genres;
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
    global $db, $frontend, $prefs;

    $media_type = 'movies';
    $npage = Filter::getInt('npage');
    $search_type = Filter::getString('search_type');
    $page = Filter::getString('page');
    empty($npage) || (!empty($search_type) && $search_type !== $media_type) ? $npage = 1 : null;

    $opt = [];
    $page_collection = '';

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
        $pager_opt['media_type'] = $media_type;
        $pager_opt['npage'] = $npage;
        $pager_opt['nitems'] = $total_collections;
        $pager_opt['get_params']['search_type'] = $media_type;
        $page_collection = $frontend->getPager($pager_opt);
        $build_opt['media_type'] = $media_type;
        $page_collection .= $frontend->buildCollection($collections, $build_opt);
    }

    return $page_collection;
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
        $master_ids[] = $vmm['id'];
    }
    /* Get Files of master ids */
    $view_files_matchs = $db->selectMultiple($library, 'master', $master_ids, 'id,master,file_hash');

    foreach ($view_files_matchs as $vfm) {
        empty($views_items[$vfm['master']]['view_items']) ? $views_items[$vfm['master']]['view_items'] = 0 : null;
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
