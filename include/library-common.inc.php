<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function rebuild($media_type, $path) {
    global $cfg, $db;


    $items = [];
    $files = findfiles($path, $cfg['media_ext']);

    if ($media_type == 'movies') {
        $db_file = 'biblio-movies';
        $ilink = 'movies_library';
    } else if ($media_type == 'shows') {
        $db_file = 'biblio-shows';
        $ilink = 'shows_library';
    }

    $media = $db->getTableData($db_file);
    $last_id = $db->getLastId($db_file);

    foreach ($files as $file) {
        if ($media === false ||
                array_search($file, array_column($media, 'path')) === false
        ) {
            $items[$last_id]['id'] = $last_id;
            $items[$last_id]['ilink'] = $ilink;
            /* File */
            $items[$last_id]['file_name'] = $file_name = trim(basename($file));
            $items[$last_id]['size'] = filesize($file);

            /* TITLE  */
            $predictible_title = getFileTitle($file_name);
            $items[$last_id]['predictible_title'] = ucwords($predictible_title);
            $items[$last_id]['title'] = '';
            $year = getFileYear($file_name);
            !empty($year) ? $items[$last_id]['year'] = $year : null;
            $items[$last_id]['path'] = $file;
            $items[$last_id]['tags'] = getFileTags($file_name);
            $items[$last_id]['ext'] = substr($file_name, -3);
            $items[$last_id]['added'] = time();
            $chapter = getFileEpisode($file_name);
            if (!empty($chapter)) {
                $items[$last_id]['season'] = intval($chapter['season']);
                $items[$last_id]['chapter'] = intval($chapter['chapter']);
            }

            /* auto identify episodes already identified */
            if ($media_type == 'shows') {
                foreach ($media as $id_item) {

                    if ($id_item['predictible_title'] === ucwords($predictible_title) &&
                            !empty($id_item['themoviedb_id'])
                    ) {
                        $items[$last_id]['themoviedb_id'] = $id_item['themoviedb_id'];
                        $items[$last_id]['title'] = $id_item['title'];
                        $items[$last_id]['poster'] = $id_item['poster'];
                        $items[$last_id]['rating'] = $id_item['rating'];
                        $items[$last_id]['popularity'] = $id_item['popularity'];
                        $items[$last_id]['scene'] = $id_item['scene'];
                        $items[$last_id]['lang'] = $id_item['lang'];
                        $items[$last_id]['plot'] = $id_item['plot'];
                        $items[$last_id]['original_title'] = $id_item['original_title'];
                    }
                }
            }
            $last_id++;
        }
    }

    isset($items) ? $db->addElements($db_file, $items) : null;

    return true;
}

function getFileTitle($file) {
    /* FIXME Better way */
    /* REGEX */
    /* GET ALL */
    $regex = '/^(?:';
    /* UNTIL */
    $regex .= '(?!\[)'; // [
    $regex .= '(?!\()'; // (
    $regex .= '(?!\()'; // (
    $regex .= '(?!-\s+\d+x)'; // '- 1x'
    $regex .= '(?!-\d+x)'; // '-1x'
    $regex .= '(?!\s\d+x)'; //space y 1x
    $regex .= '(?!_-\d+)';  // los char _- y digitos
    $regex .= '(?!_\d+_)';  // los char _digitos_
    $regex .= '(?!-\s+Temporada)';  // - Temporada
    $regex .= '(?!-\s+Season)';  // - Season
    $regex .= '(?!\d{4})'; // 4 digitos por fecha igual da problemas
    $regex .= '(?!720p)'; // 720p
    $regex .= '(?!1080p)'; // M1080p
    $regex .= '(?!M1080)'; // M1080
    $regex .= '(?!BD1080)'; // BD1080
    $regex .= '(?!2160p)'; // 2160p
    $regex .= '(?!UHD)'; // UHD
    $regex .= '(?!HD4K)'; //HD4K
    $regex .= '(?!Xvid)'; //XviD
    $regex .= '(?!DVD)'; //DVD
    $regex .= '(?!DVDRip)'; //DVDRip
    $regex .= '(?!HDRip)'; //HDRip
    $regex .= '(?!WEBRip)'; //WebRip
    $regex .= '(?!4k-hdr)'; // 4k-hdr
    $regex .= '(?!spanish)'; // spanish (dara probleams)
    $regex .= '(?!multi\senglish)'; // multi english
    $regex .= '(?!S\d{2}E\d{2})'; // SXXEXX
    $regex .= '(?!3D)'; // 3D
    $regex .= '(?!BRRip)'; // BRRIP
    $regex .= '(?!.mkv)'; //.mkv
    $regex .= '(?!.avi)'; //.avi
    $regex .= '(?!.mp4)'; //.mp4

    /* REGEX TERMINATION */
    $regex .= '.)*/i';

    $matches = [];
    preg_match($regex, $file, $matches);
    $_title = mb_strtolower($matches[0]);

    $_title = str_replace('.', ' ', $_title);
    $_title = str_replace('_', ' ', $_title);

    return trim($_title);
}

function getFileEpisode($file_name) {
    //TODO change chapter -> episode in all code

    /* FORMAT Cap.101 */
    $match = [];
    if (preg_match('/\[Cap.(.*?)\]/i', $file_name, $match) == 1) {
        $capitulo_noformat = $match[1];
        if (strlen($capitulo_noformat) == 3) {
            $temp = substr($capitulo_noformat, 0, 1);
            $cap = substr($capitulo_noformat, 1, 2);
        }
        if (strlen($capitulo_noformat) == 4) {
            $temp = substr($capitulo_noformat, 0, 2);
            $cap = substr($capitulo_noformat, 2, 3);
        }
        $chapter['season'] = $temp;
        $chapter['chapter'] = $cap;
    }

    /* FORMAT 1x01 */
    if (preg_match('/[0-9]{1,2}(x|X)[0-9]{2,2}/i', $file_name, $match) == 1) {
        $_chapter = $match[0];
        $chapter['season'] = substr($_chapter, 0, stripos($_chapter, 'x'));
        $chapter['chapter'] = substr($_chapter, stripos($_chapter, 'x') + 1);
    }

    /* FORMAT S01E01 */

    if (preg_match('/S\d{2}E\d{2}/i', $file_name, $match) == 1) {
        $matched = $match[0];
        $chapter['season'] = substr($matched, 1, stripos($matched, 'E'));
        $chapter['chapter'] = substr($matched, stripos($matched, 'E') + 1);
    }

    return isset($chapter) ? $chapter : false;
}

function getFileYear($file_name) {
    $year = '';
    $match = [];

    if (preg_match('/\([1-9]{4}\)/', $file_name, $match)) {
        isset($match[0]) ? $year = str_replace('(', '', str_replace(')', '', $match[0])) : null;
    } else if (preg_match('/[1-9]{4}/', $file_name, $match)) {
        isset($match[0]) ? $year = $match[0] : null;
    }

    return $year;
}

function getMediaType($file_name) {
    // S01E01
    if (preg_match('/S\d{2}E\d{2}/i', $file_name)) {
        return 'shows';
    }
    // 1x1 01x01
    if (preg_match('/[0-9]{1,2}(x|X)[0-9]{2,2}/i', $file_name)) {
        return 'shows';
    }
    // Cap.*
    if (preg_match('/\[Cap.(.*?)\]/i', $file_name)) {
        return 'shows';
    }
    if (preg_match('Temp\.\s+\d{1,2}\s+Capitulo\s+\d{1,2}/i', $file_name)) {
        return 'shows';
    }
    /*
      if (preg_match('',$file_name)) {
      return 'shows';
      }

     */
    return 'movies';
}

function getFileTags($file_name) {
    global $cfg;
    $tags = '';

    if (isset($cfg['MEDIA_LANGUAGE_TAG']) && count($cfg['MEDIA_LANGUAGE_TAG']) > 0) {
        foreach ($cfg['MEDIA_LANGUAGE_TAG'] as $lang_tag) {
            if (stripos($file_name, $lang_tag) !== false) {
                $tags .= '[' . $lang_tag . ']';
            }
        }
    }

    if (isset($cfg['EXTRA_TAG']) && count($cfg['EXTRA_TAG']) > 0) {
        foreach ($cfg['EXTRA_TAG'] as $extra_tag) {
            if (stripos($file_name, $extra_tag) !== false) {
                $tags .= '[' . $extra_tag . ']';
            }
        }
    }

    if (stripos($file_name, 'vose') !== false) {
        $tags .= "[VOSE]";
    }

    if (
            stripos($file_name, 'dual') !== false
    ) {
        $tags .= "[DUAL]";
    }
    if (stripos($file_name, '720p') !== false) {
        $tags .= "[720p]";
    }
    if (
            stripos($file_name, '1080p') !== false
    ) {
        $tags .= "[1080p]";
    }
    if (stripos($file_name, '480p') !== false) {
        $tags .= "[480p]";
    }
    if (stripos($file_name, 'AC3 5.1') !== false) {
        $tags .= "[AC3 5.1]";
    } else if (stripos($file_name, 'AC3') !== false) {
        $tags .= "[AC3]";
    }
    if (stripos($file_name, 'DVDRip') !== false) {
        $tags .= "[DVDRip]";
    } else if (stripos($file_name, 'DVD') !== false) {
        $tags .= "[DVD]";
    }
    if (stripos($file_name, 'DDP5.1') !== false) {
        $tags .= "[DDP5.1]";
    }
    if (stripos($file_name, 'Xvid') !== false) {
        $tags .= "[XVID]";
    }
    if (stripos($file_name, 'M1080') !== false) {
        $tags .= "[M1080]";
    }
    if (stripos($file_name, 'BD1080') !== false) {
        $tags .= "[BD1080]";
    }
    if (stripos($file_name, 'HDTV') !== false) {
        $tags .= "[HDTV]";
    }
    if (stripos($file_name, 'X264') !== false) {
        $tags .= "[x254]";
    }
    if ((stripos($file_name, 'X265') !== false) || stripos($file_name, 'HEVC') !== false) {
        $tags .= "[x265]";
    }
    if (stripos($file_name, 'HDR') !== false) {
        $tags .= "[HDR]";
    }
    if (stripos($file_name, 'BluRay') !== false) {
        $tags .= "[BluRay]";
    }
    if (stripos($file_name, '60fps') !== false) {
        $tags .= "[60FPS]";
    }
    if (stripos($file_name, 'WebRip') !== false) {
        $tags .= "[WebRip]";
    }
    if (stripos($file_name, '[3D]') !== false) {
        $tags .= "[3D]";
    }
    if (stripos($file_name, '[REMUX]') !== false) {
        $tags .= "[REMUX]";
    }
    if (stripos($file_name, '[subs]') !== false) {
        $tags .= "[SUBS]";
    }
    if (
            (stripos($file_name, 'SCREENER') !== false) ||
            (stripos($file_name, 'hd-tc') !== false)
    ) {
        $tags .= "[SCREENER]";
    }
    if (
            (stripos($file_name, 'UHD') !== false) ||
            (stripos($file_name, '4K') !== false) ||
            (stripos($file_name, '2160p') !== false)
    ) {
        $tags .= '[';
        if (stripos($file_name, '4K') !== false) {
            $tags .= "4K ";
        }
        $tags .= "UHD]";
    }
    $year = getFileYear($file_name);
    if (!empty($year)) {
        $tags .= '[' . $year . ']';
    }

    return $tags;
}

function identify_media($type, $media) {
    global $LNG, $cfg;

    $titles = '';
    $i = 0;
    $uniq_shows = [];

    $iurl = basename($_SERVER['REQUEST_URI']);
    $iurl = preg_replace('/&media_type=shows/', '', $iurl);
    $iurl = preg_replace('/&media_type=movies/', '', $iurl);
    $iurl = preg_replace('/&ident_delete=\d{1,4}/', '', $iurl);

    foreach ($media as $item) {
        if (empty($item['title'])) {
            if ($i >= $cfg['max_identify_items']) {
                break;
            }
            if ($type == 'movies') {
                $db_media = mediadb_searchMovies($item['predictible_title']);
            } else if ($type == 'shows') {
                //var_dump($item);
                if ((array_search($item['predictible_title'], $uniq_shows)) === false) {
                    $db_media = mediadb_searchShows($item['predictible_title']);
                    $uniq_shows[] = $item['predictible_title'];
                } else {
                    continue;
                }
            } else {
                return false;
            }
            $results_opt = '';
            if (!empty($db_media)) {

                foreach ($db_media as $db_item) {
                    $year = trim(substr($db_item['release'], 0, 4));
                    $results_opt .= '<option value="' . $db_item['id'] . '">';
                    $results_opt .= $db_item['title'];
                    !empty($year) ? $results_opt .= ' (' . $year . ')' : null;
                    $results_opt .= '</option>';
                }
            }
            $results_opt .= '<option value="">' . $LNG['L_NOID'] . '</option>';
            $titles .= '<div class="divTableRow"><div class="divTableCellID">' . $item['predictible_title'] . '</div>';
            $titles .= '<div class="divTableCellID">';
            if ($type == 'movies') {
                $titles .= '<select class="ident_select" name="mult_movies_select[' . $item['id'] . ']">' . $results_opt . '</select>';
            } else if ($type == 'shows') {
                $titles .= '<select class="ident_select" name="mult_shows_select[' . $item['id'] . ']">' . $results_opt . '</select>';
            }
            $titles .= '</div>';
            $titles .= '<div class="divTableCellID">';
            $titles .= '<span><a class="action_link" href="' . $iurl . '&media_type=' . $type . '&ident_delete=' . $item['id'] . '">' . $LNG['L_DELETE'] . '</a></span>';
            $titles .= '<span><a class="action_link" href="?page=identify&media_type=' . $type . '&identify=' . $item['id'] . '">' . $LNG['L_MORE'] . '</a></span>';
            $titles .= '</div>';
            $titles .= '</div>';

            $i++;
        }
    }

    if (!empty($titles)) {
        $tdata['titles'] = $titles;
        $tdata['type'] = $type;
        $tdata['head'] = $LNG['L_IDENT_' . strtoupper($type) . ''];

        $table = getTpl('identify', array_merge($LNG, $tdata));

        return $table;
    }
    return false;
}

function submit_ident($type, $items) {
    global $db;

    foreach ($items as $my_id => $db_id) {
        if (!empty($db_id)) {
            $db_item = mediadb_getById($db_id, 'tmdb_search');

            !empty($db_item['title']) ? $update_fields['title'] = $db_item['title'] : null;
            !empty($db_item['name']) ? $update_fields['title'] = $db_item['name'] : null;
            $update_fields['themoviedb_id'] = $db_item['themoviedb_id'];
            !empty($db_item['poster']) ? $update_fields['poster'] = $db_item['poster'] : null;
            !empty($db_item['chapter']) ? $update_fields['chapter'] = $db_item['chapter'] : null;
            !empty($db_item['season']) ? $update_fields['season'] = $db_item['season'] : null;
            !empty($db_item['original_title']) ? $update_fields['original_title'] = $db_item['original_title'] : null;
            !empty($db_item['rating']) ? $update_fields['rating'] = $db_item['rating'] : null;
            !empty($db_item['popularity']) ? $update_fields['popularity'] = $db_item['popularity'] : null;
            !empty($db_item['scene']) ? $update_fields['scene'] = $db_item['scene'] : null;
            !empty($db_item['lang']) ? $update_fields['lang'] = $db_item['lang'] : null;
            !empty($db_item['plot']) ? $update_fields['plot'] = $db_item['plot'] : null;
            !empty($db_item['release']) ? $update_fields['release'] = $db_item['release'] : null;
            if ($type == 'movies') {
                $db->updateRecordById('biblio-movies', $my_id, $update_fields);
            } else if ($type == 'shows') {
                $db->updateRecordsBySameField('biblio-shows', $my_id, 'predictible_title', $update_fields);
            }
        }
    }
}
