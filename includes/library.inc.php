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
    global $db, $cfg;

    $page = '';
    $npage = Filter::getInt('npage');
    $search_type = Filter::getString('search_type');
    $library = 'library_' . $media_type;
    $library_master = 'library_master_' . $media_type;

    empty($npage) || (!empty($search_type) && $search_type !== $media_type) ? $npage = 1 : null;

    if (!empty($_POST['search_term'])) {
        $search_term = Filter::postString('search_term');
    } else if (!empty($_GET['search_term'])) {
        $search_term = Filter::getString('search_term');
    }
    !empty($search_term) ? $topt['search_term'] = $search_term : null;


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
    $page .= show_identify_media($media_type);

    $rows = getPrefsItem('tresult_rows');
    empty($rows) ? $rows = $cfg['tresults_rows'] : null;
    $columns = getPrefsItem('tresult_columns');
    empty($columns) ? $columns = $cfg['tresults_columns'] : null;
    $n_results = $rows * $columns;
    $npage == 1 ? $end = $n_results : $end = $npage * $n_results;
    $npage == 1 ? $start = 0 : $start = ($npage - 1) * $n_results;

    if (empty($search_term)) {
        $topt['num_table_rows'] = $db->qSingle("SELECT COUNT(*) FROM $library_master WHERE title <> ''");
        $query = "SELECT * FROM $library_master WHERE title <> '' ORDER BY items_updated DESC LIMIT $start,$end ";
    } else {
        $topt['num_table_rows'] = $db->qSingle("SELECT COUNT(*) FROM $library_master WHERE title <> '' AND title LIKE \"%$search_term%\" ");
        $query = "SELECT * FROM $library_master WHERE title <> '' AND title LIKE \"%$search_term%\" ORDER BY items_updated DESC LIMIT $start,$end ";
    }

    $results = $db->query($query);
    $movies = $db->fetchAll($results);

    $topt['view_type'] = $media_type . '_library';
    if (valid_array($movies)) {
        $topt['search_type'] = $media_type;
        $page .= buildTable('L_' . strtoupper($media_type), $movies, $topt);
    }

    return $page;
}
