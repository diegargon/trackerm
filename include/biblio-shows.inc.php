<?php

/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function show_my_shows() {
    global $db, $cfg;

    $page = '';

    if (isset($_POST['mult_shows_select']) && !empty($_POST['mult_shows_select'])) {
        submit_ident('shows', $_POST['mult_shows_select']);
    }

    $shows = $db->getTableData('biblio-shows');

    if ($shows == false || isset($_POST['rebuild_shows'])) {
        rebuild('shows', $cfg['SHOWS_PATH']);
        $shows = $db->getTableData('biblio-shows');
    }

    if ($shows != false) {

        $page .= identify_media('shows', $shows);

        $shows_identifyed = [];

        foreach ($shows as $key => $movie) {
            if (!empty($movie['title'])) {
                $shows_identifyed[$key] = $movie;
            }
        }

        $uniq_shows = [];

        foreach ($shows_identifyed as $show) {
            $exists = false;

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
        $topt['search_type'] = 'shows';
        $page .= buildTable('L_SHOWS', $uniq_shows, $topt);
    }
    return $page;
}
