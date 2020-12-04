<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function getFileTitle($file) {
    global $cfg;

    /* Better way? */

    //regex case sensitive;
    $regex_cs = '/^(?:';
    $regex_cs .= '(?!SPANISH)';
    $regex_cs .= '(?!ENGLISH)';
    if (!empty($cfg['MEDIA_LANGUAGE_TAG']) && count($cfg['MEDIA_LANGUAGE_TAG']) > 0) {
        foreach ($cfg['MEDIA_LANGUAGE_TAG'] as $custom_media_tag) {
            $regex_cs .= '(?!' . strtoupper($custom_media_tag) . ')';
        }
    }
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
    $regex .= '(?!Bluray)'; //Bluray
    $regex .= '(?!4k-hdr)'; // 4k-hdr
    $regex .= '(?!\[spanish\])';
    $regex .= '(?!\[english\])';
    $regex .= '(?!multi\senglish)'; // multi english
    $regex .= '(?!S\d{2}E\d{2})'; // SXXEXX
    $regex .= '(?!3D)'; // 3D
    $regex .= '(?!BRRip)'; // BRRIP
    $regex .= '(?!\.mkv)'; //.mkv
    $regex .= '(?!\.avi)'; //.avi
    $regex .= '(?!\.mp4)'; //.mp4

    if (!empty($cfg['MEDIA_LANGUAGE_TAG']) && count($cfg['MEDIA_LANGUAGE_TAG']) > 0) {
        foreach ($cfg['MEDIA_LANGUAGE_TAG'] as $custom_media_tag) {
            $regex .= '(?!\[' . strtoupper($custom_media_tag) . '\])';
        }
    }

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

    /* FORMAT Cap.101 */
    $match = [];
    if (preg_match('/\[Cap.(.*?)\]/i', $file_name, $match) == 1) {
        $episode_noformat = $match[1];
        if (strlen($episode_noformat) == 3) {
            $ses = substr($episode_noformat, 0, 1);
            $epi = substr($episode_noformat, 1, 2);
        }
        if (strlen($episode_noformat) == 4) {
            $ses = substr($episode_noformat, 0, 2);
            $epi = substr($episode_noformat, 2, 3);
        }
        $episode['season'] = $ses;
        $episode['episode'] = $epi;
    }

    /* FORMAT 1x01 */
    if (preg_match('/[0-9]{1,2}(x|X)[0-9]{2,2}/i', $file_name, $match) == 1) {
        $_episode = $match[0];
        $episode['season'] = substr($_episode, 0, stripos($_episode, 'x'));
        $episode['episode'] = substr($_episode, stripos($_episode, 'x') + 1);
    }

    /* FORMAT S01E01 */

    if (preg_match('/S\d{2}E\d{2}/i', $file_name, $match) == 1) {
        $matched = $match[0];
        $episode['season'] = substr($matched, 1, stripos($matched, 'E'));
        $episode['episode'] = substr($matched, stripos($matched, 'E') + 1);
    }

    return isset($episode) ? $episode : false;
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
    // 1x1 01x01
    if (preg_match('/[0-9]{1,2}(x|X)[0-9]{2,2}/i', $file_name)) {
        return 'shows';
    }
    // Cap.*
    if (preg_match('/\[Cap.(.*?)\]/i', $file_name)) {
        return 'shows';
    }
    if (preg_match('/Temp\.\s+\d{1,2}\s+Capitulo\s+\d{1,2}/i', $file_name)) {
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

    //Would tag wrong in media where year is part of title and not a tag
    //tag wrong when Season/Episode its something like 1501
    $year = getFileYear($file_name);
    if (!empty($year)) {
        $tags .= '[' . $year . ']';
    }
    if (isset($cfg['MEDIA_LANGUAGE_TAG']) && count($cfg['MEDIA_LANGUAGE_TAG']) > 0) {
        foreach ($cfg['MEDIA_LANGUAGE_TAG'] as $lang_tag) {
            if (stripos($file_name, $lang_tag) !== false) {
                $tags .= '[' . $lang_tag . ']';
            }
        }
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
    if (isset($cfg['EXTRA_TAG']) && count($cfg['EXTRA_TAG']) > 0) {
        foreach ($cfg['EXTRA_TAG'] as $extra_tag) {
            if (stripos($file_name, $extra_tag) !== false) {
                $tags .= '[' . $extra_tag . ']';
            }
        }
    }

    return $tags;
}
