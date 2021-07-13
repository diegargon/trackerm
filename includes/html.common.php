<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

//TODO move to frontend build* and pager and html to tpl or html but before..
//TODO this functions need a rewrite, especially buildTable and Pager, too much messy... how we build the pager links, etc
function buildTable($head, $db_ary, $topt = null) {
    global $LNG, $prefs, $db;

    $npage = Filter::getInt('npage');
    empty($npage) ? $npage = 1 : null;
    $columns = $prefs->getPrefsItem('tresults_columns');
    $rows = $prefs->getPrefsItem('tresults_rows');
    $max_items = $rows * $columns;

    $page = '<div class="type_head_container">';
    if (!empty($head)) {
        $page .= '<div class="type_head"><h2>' . $LNG[$head] . '</h2></div>';
        if (!empty($topt['search_type'])) {
            $page .= '<a id="' . $topt['search_type'] . '"></a>';
        }
    }

    if (!isset($topt['no_pages'])) {
        empty($topt['num_table_rows']) ? $npages = count($db_ary) : $npages = $topt['num_table_rows'];
        $page .= pager($npage, $npages, $topt);
    }
    $page .= '</div>';

    $page .= '<div class="divTable">';
    $num_col_items = 0;
    $num_items = 0;

    if (isset($topt['num_table_rows']) ||
            $npage == 1 ||
            (isset($topt['search_type']) && isset($_GET['search_type']) && ($_GET['search_type'] != $topt['search_type']))
    ) {
        $db_ary_slice = $db_ary;
    } else {
        $npage_jump = ($max_items * $npage) - $max_items;
        $db_ary_slice = array_slice($db_ary, $npage_jump);
    }

    //For have it take titles
    //TODO: Add haveit to jackett_movies jackett_shows and add for not ask every time, must update when download
    if ($topt['view_type'] == 'movies_torrent' || $topt['view_type'] == 'shows_torrent') {
        $titles = [];
        foreach ($db_ary_slice as $item) {
            $titles[] = clean_title($item['title']);
        }
        $titles = array_unique($titles);
        if ($topt['view_type'] == 'movies_torrent') {
            $library_master = 'library_master_movies';
        } else {
            $library_master = 'library_master_shows';
        }

        $media_have = $db->selectMultiple($library_master, 'clean_title', $titles, 'id, title');

        foreach ($db_ary_slice as $key_item => $item) {
            foreach ($media_have as $item_have) {
                if (clean_title($item_have['title']) == clean_title($item['title'])) {
                    $db_ary_slice[$key_item]['have_it'] = $item_have['id'];
                    break;
                }
            }
        }
    }

    foreach ($db_ary_slice as $item) {
        if ($num_items >= $max_items) {
            break;
        }

        if (empty($topt['sizes']) && !empty($item['size'])) {
            $item['size'] = human_filesize($item['size']);
        } else if (!empty($topt['sizes'])) {
            $item['size'] = human_filesize($topt['sizes'][$item['themoviedb_id']]);
        }
        (isset($item['total_size'])) ? $item['total_size'] = human_filesize($item['total_size']) : null;

        if (!empty($item['themoviedb_id']) && !empty($topt['episode_count'][$item['themoviedb_id']])) {

            $topt['num_episodes'] = $topt['episode_count'][$item['themoviedb_id']];
        }
        !empty($topt['search_type']) ? $item['media_type'] = $topt['search_type'] : null;

        $num_col_items == 0 ? $page .= '<div class="divTableRow">' : null;
        $page .= html::div(['class' => 'divTableCell'], build_item($item, $topt));

        $num_col_items++;
        if ($num_col_items == $columns) {
            $page .= '</div>';
            $num_col_items = 0;
        }
        $num_items++;
    }
    $num_col_items != 0 ? $page .= '</div>' : false;
    $page .= '</div>';

    return $page;
}

function build_item($item, $topt) {
    global $frontend;

    $page = '';

    if (!empty($item['release']) && !empty($topt['view_type']) &&
            ($topt['view_type'] == 'movies_library' || $topt['view_type'] == 'shows_library' || $topt['view_type'] == 'shows_db' || $topt['view_type'] == 'movies_db')
    ) {
        $item['title'] = $item['title'] . ' (' . strftime("%Y", strtotime($item['release'])) . ')';
    }

    $item['poster'] = get_poster($item);

    empty($item['trailer']) && !empty($item['guessed_trailer']) && $item['guessed_trailer'] != -1 ? $item['trailer'] = $item['guessed_trailer'] : null;

    $page .= $frontend->getTpl('item_display', array_merge($item, $topt));

    return $page;
}

/* Build the pager */

function pager($npage, $nitems, &$topt) {
    global $prefs;

    $pages_links = '';
    $columns = $prefs->getPrefsItem('tresults_columns');
    $rows = $prefs->getPrefsItem('tresults_rows');
    $items_per_page = $columns * $rows;
    $num_pages = ceil($nitems / $items_per_page);
    $search_type = Filter::getUtf8('search_type');
    $page = Filter::getString('page');
    $inpage = '';

    if (isset($topt['search_type']) && $topt['search_type'] != $search_type) {
        $npage = 1;
    }
    if ($num_pages > 1) {
        $get_params = [];
        $get_params['page'] = $page;

        //Utilizado por library y new para busquedas en los resultados
        !empty($topt['search_keyword']) ? $get_params['search_keyword'] = $topt['search_keyword'] : null;
        //Utilizado dentro de view
        (!empty(Filter::getString('view_type'))) ? $get_params['view_type'] = Filter::getString('view_type') : null;

        //Poner el view id en el pager
        (!empty(Filter::getInt('id'))) ? $get_params['id'] = Filter::getInt('id') : null;

        (!empty(Filter::getUtf8('search_shows_torrents'))) ? $get_params['search_shows_torrents'] = trim(Filter::getUtf8('search_shows_torrents')) : null;
        (!empty(Filter::getUtf8('search_movies_torrents'))) ? $get_params['search_movies_torrents'] = trim(Filter::getUtf8('search_movies_torrents')) : null;
        (!empty($_GET['more_movies'])) ? $get_params['more_movies'] = 1 : null;
        (!empty($_GET['more_shows'])) ? $get_params['more_shows'] = 1 : null;
        (!empty($_GET['more_torrents'])) ? $get_params['more_torrents'] = 1 : null;
        (!empty(Filter::getUtf8('search_movies_db'))) ? $get_params['search_movies_db'] = trim(Filter::getUtf8('search_movies_db')) : null;
        (!empty(Filter::getUtf8('search_shows_db'))) ? $get_params['search_shows_db'] = trim(Filter::getUtf8('search_shows_db')) : null;
        (!empty(Filter::getUtf8('search_movies'))) ? $get_params['search_movies'] = trim(Filter::getUtf8('search_movies')) : null;
        (!empty(Filter::getUtf8('search_shows'))) ? $get_params['search_shows'] = trim(Filter::getUtf8('search_shows')) : null;

        for ($i = 1; $i <= ceil($num_pages); $i++) {
            if (($i == 1 || $i == $num_pages || $i == $npage) ||
                    in_range($i, ($npage - 3), ($npage + 3), TRUE)
            ) {

                $link_npage_class = "num_pages_link";

                if (!empty($topt['search_type'])) {
                    $get_params['search_type'] = $topt['search_type'];
                    $inpage = $topt['search_type'];
                }

                if (isset($npage) && ($npage == $i)) {
                    if (isset($topt['search_type']) && ($search_type != $topt['search_type'])) {

                    } else {
                        $link_npage_class .= '_selected';
                    }
                }
                $get_params['npage'] = $i;

                $pages_links .= html::link(['onClick' => 'show_loading()', 'inpage' => $inpage, 'class' => $link_npage_class], '', $i, $get_params);
            }
        }
    }

    return html::div(['class' => 'type_pages_numbers'], $pages_links);
}

function html_mediainfo_tags($mediainfo, $tags = null) {
    global $LNG;

    $general_tags = $video_tags = $audio_tags = $text_tags = '';
    $tags = [
        'General' => ['Format', 'FrameRate', 'AudioCount', 'VideoCount', 'TextCount'],
        'Video' => ['FrameRate_Mode', 'ColorSpace', 'Encoded_Library_Name'],
        'Audio' => ['Format', 'BitRate_Mode', 'Format_Commercial_IfAny', 'Channels', 'BitRate', 'Compression_Mode'],
//        'Text' => '',
    ];
    foreach ($tags as $tag_ary_key => $tag_ary) {
        if ($tag_ary_key == 'General') {
            foreach ($tag_ary as $tag_value) {
                isset($mediainfo[$tag_ary_key][$tag_value]) ? $general_tags .= '<div  title="' . $tag_value . '" class="mediainfo_tag">' . $mediainfo[$tag_ary_key][$tag_value] . '</div>' : null;
            }
        }
        if ($tag_ary_key == 'Video') {
            foreach ($tag_ary as $tag_value) {
                isset($mediainfo[$tag_ary_key][0][$tag_value]) ? $video_tags .= '<div  title="' . $tag_value . '" class="mediainfo_tag">' . $mediainfo[$tag_ary_key][0][$tag_value] . '</div>' : null;
            }
        }
        if ($tag_ary_key == 'Audio') {
            foreach ($tag_ary as $tag_value) {
                isset($mediainfo[$tag_ary_key][1][$tag_value]) ? $audio_tags .= '<div title="' . $tag_value . '" class="mediainfo_tag">' . $mediainfo[$tag_ary_key][1][$tag_value] . '</div>' : null;
            }
        }
        if ($tag_ary_key == 'Text') {
            foreach ($tag_ary as $tag_value) {
                isset($mediainfo[$tag_ary_key][1][$tag_value]) ? $text_tags .= '<div  title="' . $tag_value . '" class="mediainfo_tag">' . $mediainfo[$tag_ary_key][1][$tag_value] . '</div>' : null;
            }
        }
    }

    if (($mediainfo['Video'][0]['Width']) && isset($mediainfo['Video'][0]['Height'])) {
        $video_tags .= '<div class="mediainfo_tag">' . $mediainfo['Video'][0]['Width'] . 'x' . $mediainfo['Video'][0]['Height'] . 'p</div>';
    }
    if (isset($mediainfo['Audio'])) {
        foreach ($mediainfo['Audio'] as $audio) {
            isset($audio['Language']) ? $audio_tags .= '<div title="' . $LNG['L_AUDIO'] . '" class="mediainfo_tag">' . $audio['Language'] . '</div>' : null;
        }
    }

    return $general_tags . $video_tags . $audio_tags . $text_tags;
}
