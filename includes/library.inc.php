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
    global $db, $prefs, $user;

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
    $npage == 1 ? $end = $n_results : $end = $npage * $n_results;
    $npage == 1 ? $start = 0 : $start = ($npage - 1) * $n_results;

    /*
     * Get view media, select masters and check view items vs total items,
     * if results is 0 get the id for not show the master
     */

    $where_view_data = [
        'uid' => ['value' => $user->getId()],
        'media_type' => ['value' => $media_type],
    ];
    $results = $db->select('view_media', '*', $where_view_data);
    $views_data = $db->fetchAll($results);
    $views_tmdb_ids = [];
    foreach ($views_data as $view_data) {
        $views_tmdb_ids[] = $view_data['themoviedb_id'];
    }
    $views_tmdb_ids_uniq = array_unique($views_tmdb_ids);
    $view_master_matchs = $db->selectMultiple($library_master, 'themoviedb_id', $views_tmdb_ids_uniq, 'id,themoviedb_id, total_items');
    $views_items = [];
    foreach ($view_master_matchs as $vmm) {
        $views_items[$vmm['id']]['total_items'] = $vmm['total_items'];
        $views_items[$vmm['id']]['view_items'] = 0;
        foreach ($views_tmdb_ids as $vti) {
            if ($vmm['themoviedb_id'] == $vti) {
                $views_items[$vmm['id']]['view_items']++;
            }
        }
    }
    if ($prefs->getPrefsItem('view_mode')) {
        $ignore_master_ids = '';
        foreach ($views_items as $key_view_item => $view_item) {
            if (($view_item['total_items'] - $view_item['view_items']) <= 0) {
                empty($ignore_master_ids) ? $ignore_master_ids .= $key_view_item : $ignore_master_ids .= ',' . $key_view_item;
            }
        }
    }

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
    $query = "SELECT * FROM $library_master $where ORDER BY items_updated DESC LIMIT $start,$end ";

    $results = $db->query($query);
    $media = $db->fetchAll($results);

    //Rest view_item count to total_items
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
