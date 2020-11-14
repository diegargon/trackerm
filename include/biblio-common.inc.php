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

    $files = findfiles($path, $cfg['media_ext']);

    $i = 0;

    if ($media_type == 'movies') {
        $db_file = 'biblio-movies';
        $ilink = 'movies_library';
    } else if ($media_type == 'shows') {
        $db_file = 'biblio-shows';
        $ilink = 'shows_library';
    }

    $media = $db->getTableData($db_file);

    foreach ($files as $file) {
        if ($media === false ||
                array_search($file, array_column($media, 'path')) === false
        ) {

            $items[$i]['id'] = $i;
            $items[$i]['ilink'] = $ilink;
            /* File */
            $items[$i]['file_name'] = $file_name = trim(basename($file));
            $items[$i]['size'] = filesize($file);

            /* TITLE  */
            $predictible_title = getFileTitle($file_name);
            $items[$i]['predictible_title'] = ucwords($predictible_title);
            $items[$i]['title'] = '';
            $year = getFileYear($file_name);
            !empty($year) ? $items[$i]['year'] = $year : null;
            $items[$i]['path'] = $file;
            $items[$i]['tags'] = getFileTags($file_name);
            $items[$i]['ext'] = substr($file_name, -3);
            $items[$i]['added'] = time();
            $chapter = getFileChapter($file_name);
            if (!empty($chapter)) {
                $items[$i]['season'] = $chapter['season'];
                $items[$i]['chapter'] = $chapter['chapter'];
            }

            $i++;
        }
    }

    isset($items) ? $db->addElements($db_file, $items) : null;

    return true;
}

function identify_media($type, $media) {
    global $LNG, $cfg;

    $titles = '';
    $i = 0;
    $uniq_shows = [];

    foreach ($media as $item) {
        if (empty($item['title'])) {
            // Maximo 5 peliculas a identidfiar de cada vez, FIXME: break
            if ($i >= $cfg['max_identify_items']) {
                break;
            }
            if ($type == 'movies') {
                $db_media = db_search_movies($item['predictible_title']);
            } else if ($type == 'shows') {
                //var_dump($item);
                if ((array_search($item['predictible_title'], $uniq_shows)) === false) {
                    $db_media = db_search_shows($item['predictible_title']);
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
                    $year = substr($db_item['release'], 0, 4);
                    $results_opt .= '<option value="' . $db_item['id'] . '">' . $db_item['title'] . ' (' . $year . ')</option>';
                }
                $results_opt .= '<option value="">' . $LNG['L_NOID'] . '</option>';
            }
            $titles .= '<tr><td>' . $item['predictible_title'] . '</td><td>';
            if ($type == 'movies') {
                $titles .= '<select name="mult_movies_select[' . $item['id'] . ']">' . $results_opt . '</select>';
            } else if ($type == 'shows') {
                $titles .= '<select name="mult_shows_select[' . $item['id'] . ']">' . $results_opt . '</select>';
            }
            $titles .= '</td></tr>';

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

function getFileTitle($file) {
    /* FIXME Better way */
    /* REGEX */
    /* GET ALL */
    $regex = '/^(?:';
    /* UNTIL */
    $regex .= '(?!\[)'; // [
    $regex .= '(?!\()'; // (
    $regex .= '(?!\()'; // (
    $regex .= '(?!-\d+x)'; // -1x
    $regex .= '(?!\s\d+x)'; //space y 1x 
    $regex .= '(?!_-\d+)';  // los char _- y digitos
    $regex .= '(?!_\d+_)';  // los char _digitos_
    $regex .= '(?!\d{4})'; // 4 digitos por fecha igual da problemas
    $regex .= '(?!M1080)'; // M1080
    $regex .= '(?!BD1080)'; // BD1080
    $regex .= '(?!HD4K)'; //HD4K        
    $regex .= '(?!Xvid)'; //XviD
    $regex .= '(?!DVD)'; //DVD
    $regex .= '(?!DVDRip)'; //DVDRip        
    $regex .= '(?!HDRip)'; //HDRip
    $regex .= '(?!4k-hdr)'; // 4k-hdr
    $regex .= '(?!spanish)'; // spanish (dara probleams)
    $regex .= '(?!multi\senglish)'; // multi english
    $regex .= '(?!S\d{2}E\d{2})'; // SXXEXX
    $regex .= '(?!.mkv)'; //.mkv
    $regex .= '(?!.avi)'; //.avi
    $regex .= '(?!.mp4)'; //.mp4

    /* REGEX TERMINATION */
    $regex .= '.)*/i';

    preg_match($regex, $file, $matches);
    $_title = mb_strtolower($matches[0]);

    $_title = str_replace('.', ' ', $_title);
    $_title = str_replace('_', ' ', $_title);

    return trim($_title);
}

function getFileChapter($file_name) {

    /* FORMAT Cap.101 */
    if (preg_match('/\[Cap.(.*?)\]/i', $file_name, $match) == 1) {
        $capitulo_noformat = $match[1];
        if (strlen($capitulo_noformat) == 3) {
            $temp = substr($capitulo_noformat, 0, 1);
            $cap = substr($capitulo_noformat, 1, 2);
            $cap = "-" . $temp . "x" . $cap;
        }
        if (strlen($capitulo_noformat) == 4) {
            $temp = substr($capitulo_noformat, 0, 2);
            $cap = substr($capitulo_noformat, 2, 3);
            $cap = "-" . $temp . "x" . $cap;
        }
        $chapter['season'] = $temp;
        $chapter['cap'] = $cap;
    }

    /* FORMAT 1x01 */
    if (preg_match('/[0-9]{1,2}(x|X)[0-9]{2,2}/i', $file_name, $match) == 1) {
        $_chapter = $match[0];
        $chapter['season'] = substr($_chapter, 0, stripos($_chapter, 'x'));
        $chapter['chapter'] = substr($_chapter, stripos($_chapter, 'x') + 1);
    }
    return isset($chapter) ? $chapter : false;
}

function getFileYear($file_name) {
    $year = '';

    if (preg_match('/\([1-9]{4}\)/', $file_name, $match)) {
        isset($match[0]) ? $year = str_replace('(', '', str_replace(')', '', $match[0])) : null;
    } else if (preg_match('/[1-9]{4}/', $file_name, $match)) {
        isset($match[0]) ? $year = $match[0] : null;
    }

    return $year;
}

function getFileTags($file_name) {
    $tags = '';
    if (stripos($file_name, '720p') !== false) {
        $tags .= "[720p]";
    }
    if (stripos($file_name, '1080p') !== false) {
        $tags .= "[1080p]";
    }
    if (stripos($file_name, 'AC3 5.1') !== false) {
        $tags .= "[AC3 5.1]";
    } else if (stripos($file_name, 'AC3') !== false) {
        $tags .= "[AC3]";
    }
    if (stripos($file_name, 'M1080') !== false) {
        $tags .= "[M1080]";
    }
    if (stripos($file_name, 'BD1080') !== false) {
        $tags .= "[BD1080]";
    }
    $year = getFileYear($file_name);
    if (!empty($year)) {
        $tags .= '[' . $year . ']';
    }

    return $tags;
}

function submit_ident($type, $items) {
    global $db;

    foreach ($items as $my_id => $db_id) {
        if (!empty($db_id)) {
            $db_item = db_get_byid($db_id, 'tmdb_search');

            !empty($db_item['title']) ? $update_fields['title'] = $db_item['title'] : null;
            !empty($db_item['name']) ? $update_fields['title'] = $db_item['name'] : null;
            $update_fields['themoviedb'] = $db_item['id'];
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
                $db->updateRecordByID('biblio-movies', $my_id, $update_fields);
            } else if ($type == 'shows') {
                $db->updateRecordsBySameField('biblio-shows', $my_id, 'predictible_title', $update_fields);
            }
        }
    }
}
