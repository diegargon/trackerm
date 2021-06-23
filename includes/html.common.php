<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

//TODO move to frontend and html to tpl or html::
function buildTable($head, $db_ary, $topt = null) {
    global $cfg, $LNG;

    $npage = Filter::getInt('npage');
    empty($npage) ? $npage = 1 : null;

    empty($topt['columns']) ? $columns = $cfg['tresults_columns'] : $columns = $topt['columns'];
    empty($topt['max_items']) ? $max_items = $cfg['tresults_rows'] * $columns : $max_items = $topt['max_items'];

    $page = '<div class="type_head_container">';

    !empty($head) ? $page .= '<div class="type_head"><h2>' . $LNG[$head] . '</h2></div>' : null;

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
    global $cfg, $frontend;

    $page = '';

    if (!empty($item['release']) && !empty($topt['view_type']) &&
            ($topt['view_type'] == 'movies_library' || $topt['view_type'] == 'shows_library' || $topt['view_type'] == 'shows_db' || $topt['view_type'] == 'movies_db')
    ) {
        $item['title'] = $item['title'] . ' (' . strftime("%Y", strtotime($item['release'])) . ')';
    }

    if ($cfg['cache_images']) {
        if (!empty($item['poster'])) {
            $cache_img_response = cache_img($item['poster']);
            if ($cache_img_response !== false) {
                $item['poster'] = $cache_img_response;
            }
        } else if (!empty($item['guessed_poster']) && $item['guessed_poster'] != -1) {
            $cache_img_response = cache_img($item['guessed_poster']);
            if ($cache_img_response !== false) {
                $item['poster'] = $cache_img_response;
            }
        }
    } else if (empty($item['poster']) && !empty($item['guessed_poster'])) {
        $item['pòster'] = $item['guessed_poster'];
    }
    empty($item['poster']) ? $item['poster'] = $cfg['img_url'] . '/not_available.jpg' : null;

    $page .= $frontend->getTpl('item_display', array_merge($item, $topt));

    return $page;
}

function pager($npage, $nitems, &$topt) {
    global $cfg;

    /* PAGES */

    $pages_links = '';
    $items_per_page = $cfg['tresults_columns'] * $cfg['tresults_rows'];
    $num_pages = ceil($nitems / $items_per_page);
    $search_type = Filter::getUtf8('search_type');
    $page = Filter::getString('page');

    if ($num_pages > 1) {
        $get_params = [];
        $get_params['page'] = $page;
        (!empty(Filter::getString('view_type'))) ? $get_params['view_type'] = Filter::getString('view_type') : null;
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
        !empty($topt['search_term']) ? $get_params['search_term'] = $topt['search_term'] : null;

        for ($i = 1; $i <= ceil($num_pages); $i++) {
            if (($i == 1 || $i == $num_pages || $i == $npage) ||
                    in_range($i, ($npage - 3), ($npage + 3), TRUE)
            ) {

                $link_npage_class = "num_pages_link";

                if (!empty($topt['search_type'])) {
                    $get_params['search_type'] = $topt['search_type'];
                }

                if (isset($npage) && ($npage == $i)) {
                    if (isset($topt['search_type']) && ($search_type != $topt['search_type'])) {

                    } else {
                        $link_npage_class .= '_selected';
                    }
                }
                $get_params['npage'] = $i;

                $pages_links .= html::link(['onClick' => 'show_loading()', 'class' => $link_npage_class], '', $i, $get_params);
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
