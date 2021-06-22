<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function show_my_movies() {
    global $db, $cfg;

    $page = '';
    $npage = Filter::getInt('npage');
    $search_type = Filter::getString('search_type');

    if (!empty($_POST['search_term'])) {
        $search_term = Filter::postString('search_term');
    } else if (!empty($_GET['search_term'])) {
        $search_term = Filter::getString('search_term');
    }
    !empty($search_term) ? $topt['search_term'] = $search_term : null;
    empty($npage) || (!empty($search_type) && $search_type == 'shows') ? $npage = 1 : null;

    if (!empty($_POST['mult_movies_select'])) {
        ident_by_idpairs('movies', $_POST['mult_movies_select']);
    }
    if (!empty(($ident_delete = Filter::getInt('ident_delete'))) && ($_GET['media_type'] == 'movies')) {
        $db->deleteItemById('library_movies', $ident_delete);
    }
    $page .= show_identify_media('movies');

    $rows = getPrefsItem('tresult_rows');
    empty($rows) ? $rows = $cfg['tresults_rows'] : null;
    $columns = getPrefsItem('tresult_columns');
    empty($columns) ? $columns = $cfg['tresults_columns'] : null;
    $n_results = $rows * $columns;
    $npage == 1 ? $end = $n_results : $end = $npage * $n_results;
    $npage == 1 ? $start = 0 : $start = ($npage - 1) * $n_results;

    if (empty($search_term)) {
        $topt['num_table_rows'] = $db->qSingle("SELECT COUNT(*) FROM library_master_movies WHERE title <> ''");
        $query = "SELECT * FROM library_master_movies WHERE title <> '' ORDER BY updated DESC LIMIT $start,$end ";
    } else {
        $topt['num_table_rows'] = $db->qSingle("SELECT COUNT(*) FROM library_master_movies WHERE title <> '' AND title LIKE \"%$search_term%\" ");
        $query = "SELECT * FROM library_master_movies WHERE title <> '' AND title LIKE \"%$search_term%\" ORDER BY items_updated DESC LIMIT $start,$end ";
    }

    $results = $db->query($query);
    $movies = $db->fetchAll($results);

    $topt['view_type'] = 'movies_library';
    if (valid_array($movies)) {
        $topt['search_type'] = 'movies';
        $page .= buildTable('L_MOVIES', $movies, $topt);
    }

    return $page;
}

function show_my_shows() {
    global $db, $cfg;

    $page = '';
    $topt = [];
    $npage = Filter::getInt('npage');
    $search_type = Filter::getString('search_type');

    if (!empty($_POST['search_term'])) {
        $search_term = Filter::postString('search_term');
    } else if (!empty($_GET['search_term'])) {
        $search_term = Filter::getString('search_term');
    }
    !empty($search_term) ? $topt['search_term'] = $search_term : null;
    empty($npage) || (!empty($search_type) && $search_type == 'movies') ? $npage = 1 : null;

    if (isset($_POST['mult_shows_select']) && !empty($_POST['mult_shows_select'])) {
        ident_by_idpairs('shows', $_POST['mult_shows_select']);
    }

    if (!empty($_GET['ident_delete']) && ($_GET['media_type'] == 'shows')) {
        $delete_ident_item = $db->getItemById('library_shows', $_GET['ident_delete']);
        $delete_ptitle_match = $delete_ident_item['predictible_title'];
        $db->deleteItemsByField('library_shows', 'predictible_title', $delete_ptitle_match);
    }
    $page .= show_identify_media('shows');

    $rows = getPrefsItem('tresult_rows');
    empty($rows) ? $rows = $cfg['tresults_rows'] : null;
    $columns = getPrefsItem('tresult_columns');
    empty($columns) ? $columns = $cfg['tresults_columns'] : null;
    $n_results = $rows * $columns;
    $npage == 1 ? $end = $n_results : $end = $npage * $n_results;
    $npage == 1 ? $start = 0 : $start = ($npage - 1) * $n_results;

    if (empty($search_term)) {
        $topt['num_table_rows'] = $db->qSingle("SELECT COUNT(*) FROM library_master_shows WHERE title <> ''");
        $query = "SELECT * FROM library_master_shows WHERE title <> '' ORDER BY updated DESC LIMIT $start,$end ";
    } else {
        $topt['num_table_rows'] = $db->qSingle("SELECT COUNT(*) FROM library_master_shows WHERE title <> '' AND title LIKE \"%$search_term%\" ");
        $query = "SELECT * FROM library_master_shows WHERE title <> '' AND title LIKE \"%$search_term%\" ORDER BY items_updated DESC LIMIT $start,$end ";
    }
    $results = $db->query($query);
    $shows = $db->fetchAll($results);

    if (valid_array($shows)) {
        $topt['view_type'] = 'shows_library';
        $topt['search_type'] = 'shows';

        $page .= buildTable('L_SHOWS', $shows, $topt);
    }
    return $page;
}
