<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function getFileTitle($file) {

    /* Better way? */

    //regex case sensitive;
    $regex_cs = '/^(?:';
    $regex_cs .= '(?!SPANISH)';
    $regex_cs .= '(?!ENGLISH)';
    $regex_cs .= '(?!CASTELLANO)';
    $regex_cs .= '(?!ESPAÑOL)';
    $regex_cs .= '(?!LATINO)';
    $regex_cs .= '.)*/';
    $matches = [];
    preg_match($regex_cs, $file, $matches);
    $file = mb_strtolower($matches[0]);


    /* REGEX  case insentive */
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
    $regex .= '(?!.\d+x)';  // .digits and x (.1x)
    $regex .= '(?!_\d+x)';  // .digits and x (_1x)
    $regex .= '(?!-\s+Temporada)';  // - Temporada
    $regex .= '(?!\s+Temporada)';  //  Temporada
    $regex .= '(?!-\s+Season)';  // - Season
    $regex .= '(?!720p)'; // 720p
    $regex .= '(?!1080p)'; // M1080p
    $regex .= '(?!M1080)'; // M1080
    $regex .= '(?!BD1080)'; // BD1080
    $regex .= '(?!2160p)'; // 2160p
    $regex .= '(?!4K)'; //4K
    $regex .= '(?!UHD)'; // UHD
    $regex .= '(?!HD4K)'; //HD4K
    $regex .= '(?!Xvid)'; //XviD
    $regex .= '(?!DVD)'; //DVD
    $regex .= '(?!DVDRip)'; //DVDRip
    $regex .= '(?!HDRip)'; //HDRip
    $regex .= '(?!WEBRip)'; //WebRip
    $regex .= '(?!Bluray)'; //Bluray
    $regex .= '(?!multi\senglish)'; // multi english
    $regex .= '(?!S\d{2}E\d{2})'; // SXXEXX
    $regex .= '(?!S\d{2}\s+E\d{2})'; // SXX EXX
    $regex .= '(?!3D)'; // 3D
    $regex .= '(?!BRRip)'; // BRRIP
    $regex .= '(?!\.mkv$)'; //.mkv
    $regex .= '(?!\.avi$)'; //.avi
    $regex .= '(?!\.mp4$)'; //.mp4

    /* REGEX TERMINATION */
    $regex .= '.)*/i';

    $matches = [];
    preg_match($regex, $file, $matches);
    $_title = mb_strtolower($matches[0]);

    $_title = str_replace('.', ' ', $_title);
    $_title = str_replace('_', ' ', $_title);

    //Remove year, this can cause problems for titles if the year is a real part of title
    //Here only check if remove year we get empty title, that avoid break media titles where the year is only
    //word in the title like movie '1917', 1944, etc
    $year_regex = '/^(?:';
    $year_regex .= '(?!\d{4})';
    $year_regex .= '.)*/i';
    preg_match($regex, $_title, $matches);
    $without_year_title = $matches[0];

    (empty($without_year_title)) ? $_title = $without_year_title : $_title = $_title;

    return trim($_title);
}

function getFileEpisode($file_name) {

    $SE = [];

    /* FORMAT S01E01 */
    if (preg_match('/S\d{2}E\d{2}/i', $file_name, $match) == 1) {
        $matched = trim($match[0]);
        $SE['season'] = substr($matched, 1, 2);
        $SE['episode'] = substr($matched, stripos($matched, 'E') + 1);

        return $SE;
    }

    /* FORMAT S01 E01 */
    if (preg_match('/S\d{2}\s+E\d{2}/i', $file_name, $match) == 1) {
        $matched = trim($match[0]);
        $SE['season'] = trim(substr($matched, 1, 2));
        $SE['episode'] = trim(substr($matched, stripos($matched, 'E') + 1));
        return $SE;
    }

    /* FORMAT Cap.101 */
    $match = [];
    if (preg_match('/\[Cap.(.*?)\]/i', $file_name, $match) == 1) {
        $SE_MATCH = trim($match[1]);
        if (strlen($SE_MATCH) == 3) {
            $ses = substr($SE_MATCH, 0, 1);
            $epi = substr($SE_MATCH, 1, 2);
        }
        if (strlen($SE_MATCH) > 3) {
            $ses = substr($SE_MATCH, 0, 2);
            $epi = substr($SE_MATCH, 2, 3);
        }

        $SE['season'] = $ses;
        $SE['episode'] = $epi;

        return $SE;
    }

    /* FORMAT 1x01 */
    if (preg_match('/[0-9]{1,2}(x)[0-9]{2,2}/i', $file_name, $match) == 1) {
        $SE_MATCH = trim($match[0]);
        $SE['season'] = substr($SE_MATCH, 0, stripos($SE_MATCH, 'x'));
        $SE['episode'] = substr($SE_MATCH, stripos($SE_MATCH, 'x') + 1);

        return $SE;
    }
    /* FORMAT 1x1 */
    if (preg_match('/[0-9]{1,2}(x)[0-9]{1,2}/i', $file_name, $match) == 1) {
        $SE_MATCH = trim($match[0]);
        $SE['season'] = substr($SE_MATCH, 0, stripos($SE_MATCH, 'x'));
        $SE['episode'] = substr($SE_MATCH, stripos($SE_MATCH, 'x') + 1);

        return $SE;
    }

    $file_name = str_replace(['480p', '720p', '1080p', '2160p'], '', $file_name);
    /* FORMAT 000  not temp , this must the near the last check */
    if (preg_match('/[0-9]{3}/', $file_name, $match)) {
        $SE['season'] = 1;
        $SE['episode'] = $match[0];
        return $SE;
    }

    return false;
}

function getFileYear($file_name) {
    $year = '';
    $match = [];

    if (preg_match('/\([1-9]{4}\)/', $file_name, $match)) {
        isset($match[0]) ? $year = str_replace('(', '', str_replace(')', '', $match[0])) : $year = false;
    } else if (preg_match('/[1-9]{4}/', $file_name, $match)) {
        isset($match[0]) ? $year = $match[0] : $year = false;
    }

    return $year;
}

function getMediaType($file_name) {
    // S01E01
    if (preg_match('/S\d{2}E\d{2}/i', $file_name)) {
        return 'shows';
    }
    // S01 E01
    if (preg_match('/S\d{2}\s+E\d{2}/i', $file_name)) {
        return 'shows';
    }
    // 1x1 01x01
    if (preg_match('/[0-9]{1,2}(x|X)[0-9]{1,2}/i', $file_name)) {
        return 'shows';
    }
    // Cap.*
    if (preg_match('/\[Cap.(.*?)\]/i', $file_name)) {
        return 'shows';
    }
    //Temp. 1 Capitulo 1
    if (preg_match('/Temp\.\s+\d{1,2}\s+Capitulo\s+\d{1,2}/i', $file_name)) {
        return 'shows';
    }
    //Season 1
    if (preg_match('/season\s+\d{1,2}/i', $file_name)) {
        return 'shows';
    }
    //Seasons 1
    if (preg_match('/seasons\s+\d{1,2}/i', $file_name)) {
        return 'shows';
    }
    //Temporada 1
    if (preg_match('/Temporada\s+\d{1,2}/i', $file_name)) {
        return 'shows';
    }
    // T1*Completa
    if (preg_match('/T\d{1,2}.+Completa/i', $file_name)) {
        return 'shows';
    }
    // T1*Full
    if (preg_match('/T\d{1,2}.+Full/i', $file_name)) {
        return 'shows';
    }
    // Temp 1
    if (preg_match('/Temp.+\d{1,2}/i', $file_name)) {
        return 'shows';
    }
    return 'movies';
}

function getFileTags($file_name) {
    global $cfg;
    $tags = '';

    if (isset($cfg['extra_tags']) && count($cfg['extra_tags']) > 0) {
        foreach ($cfg['extra_tags']as $extra_tag) {
            if (stripos($file_name, $extra_tag) !== false) {
                $tags .= '[' . $extra_tag . ']';
            }
        }
    }
    //Would tag wrong in media where year is part of title and not a tag
    //tag wrong when Season/Episode its something like 1501
    $year = getFileYear($file_name);
    if (!empty($year)) {
        $tags .= '[' . $year . ']';
    }

    if (stripos($file_name, 'vose') !== false) {
        $tags .= "[VOSE]";
    }
    if (stripos($file_name, 'dual') !== false) {
        $tags .= "[DUAL]";
    }
    if (stripos($file_name, '720p') !== false) {
        $tags .= "[720p]";
    }
    if (stripos($file_name, '1080p') !== false) {
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
    if (stripos($file_name, 'DTS 5.1') !== false) {
        $tags .= "[DTS 5.1]";
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

    return $tags;
}
