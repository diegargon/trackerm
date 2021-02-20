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
    global $db, $filter, $cfg;

    $page = '';

    $npage = $filter->getInt('npage');
    $search_type = $filter->getString('search_type');

    empty($npage) || (!empty($search_type) && $search_type == 'shows') ? $npage = 1 : null;

    if (!empty($_POST['mult_movies_select'])) {
        ident_by_idpairs('movies', $_POST['mult_movies_select']);
    }
    if (!empty(($ident_delete = $filter->getInt('ident_delete'))) && ($_GET['media_type'] == 'movies')) {
        $db->deleteItemById('library_movies', $ident_delete);
    }
    $page .= show_identify_media('movies');

    $rows = getPrefsItem('tresult_rows');
    empty($rows) ? $rows = $cfg['tresults_rows'] : null;
    $columns = getPrefsItem('tresult_columns');
    empty($columns) ? $columns = $cfg['tresults_columns'] : null;
    $n_results = $rows * $columns;
    $npage == 1 ? $end = $n_results : $end = $npage * $n_results;
    $npage == 1 ? $start = 1 : $start = ($npage - 1) * $n_results;

    $topt['num_table_rows'] = $db->qSingle("SELECT COUNT(*) FROM library_movies WHERE title IS NOT NULL OR title != ''");
    $query = "SELECT * FROM library_movies WHERE title IS NOT NULL OR title != '' ORDER BY created DESC LIMIT $start,$end ";
    $stmt = $db->query($query);
    $movies = $db->fetchAll($stmt);

    $topt['view_type'] = 'movies_library';
    if (valid_array($movies)) {
        $topt['search_type'] = 'movies';
        $page .= buildTable('L_MOVIES', $movies, $topt);
    }

    return $page;
}

function show_my_shows() {
    global $db;

    $page = '';
    $topt = [];

    if (isset($_POST['mult_shows_select']) && !empty($_POST['mult_shows_select'])) {
        ident_by_idpairs('shows', $_POST['mult_shows_select']);
    }

    if (!empty($_GET['ident_delete']) && ($_GET['media_type'] == 'shows')) {
        $delete_ident_item = $db->getItemById('library_shows', $_GET['ident_delete']);
        $delete_ptitle_match = $delete_ident_item['predictible_title'];
        $db->deleteItemsByField('library_shows', 'predictible_title', $delete_ptitle_match);
    }

    $page .= show_identify_media('shows');

    //TODO: for improve and select only from table what we want show we need
    //add a total_field size to library_shows and update with tracker-cli then i
    //can show size without get all data.

    $shows = $db->getTableData('library_shows');

    if (valid_array($shows)) {

        $shows_identifyed = [];

        foreach ($shows as $key => $movie) {
            if (!empty($movie['title'])) {
                $shows_identifyed[$key] = $movie;
            }
        }

        usort($shows_identifyed, function ($a, $b) {
            return strcmp($a["created"], $b["created"]);
        });
        $shows_identifyed = array_reverse($shows_identifyed);

        $uniq_shows = [];
        $sum_sizes = [];
        $episode_count = [];

        foreach ($shows_identifyed as $show) {
            $exists = false;

            if (isset($sum_sizes[$show['themoviedb_id']])) {
                $sum_sizes[$show['themoviedb_id']] = $show['size'] + $sum_sizes[$show['themoviedb_id']];
            } else {
                $sum_sizes[$show['themoviedb_id']] = $show['size'];
            }

            if (isset($episode_count[$show['themoviedb_id']])) {
                $episode_count[$show['themoviedb_id']] = 1 + $episode_count[$show['themoviedb_id']];
            } else {
                $episode_count[$show['themoviedb_id']] = 1;
            }

            foreach ($uniq_shows as $item) {
                if ($item['title'] == $show['title']) {
                    $exists = true;
                    break;
                } else {
                    $exists = false;
                }
            }

            $exists === false ? $uniq_shows[] = $show : null;
        }
        count($sum_sizes) > 1 ? $topt['sizes'] = $sum_sizes : null;
        count($episode_count) > 1 ? $topt['episode_count'] = $episode_count : null;

        $topt['view_type'] = 'shows_library';
        $topt['search_type'] = 'shows';

        $page .= buildTable('L_SHOWS', $uniq_shows, $topt);
    }
    return $page;
}
