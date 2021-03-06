<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
function get_pl_movies($item_requested) {
    global $cfg;

    $path = str_replace($cfg['playlocal_root_path'], '', $item_requested['path']);
    $m3u_title = "#EXTINF:-1, {$item_requested['title']}\r\n";
    //$header_title = $item['title'];
    if (isMobile() || isLinux()) {
        $m3u_path = $cfg['playlocal_share_linux_path'] . $path . "\r\n";
    } else {
        $m3u_path = $cfg['playlocal_share_windows_path'] . $path . "\r\n";
    }
    return $m3u_title . $m3u_path;
}

//"#EXTVLCOPT:start-time=100"; (start time)
function get_pl_shows($item_requested) {
    global $db, $cfg;

    $items = $db->getItemsByField('library_shows', 'master', $item_requested['master']);

    if (!valid_array($items)) {
        exit();
    }

    usort($items, function ($a, $b) {
        $season = $a['season'] - $b['season'];
        if ($season === 0) {
            return $a['episode'] - $b['episode'];
        }
        return $season;
    });

    $m3u_playlist = '';
    foreach ($items as $item) {
        if (($item['season'] < $item_requested['season']) ||
                ($item['season'] == $item_requested['season'] && $item['episode'] < $item_requested['episode'])
        ) {
            continue;
        }
        $match = [];
        if (preg_match('/S\d{2}E\d{2}/i', $item['file_name'], $match) == 1) {
            $episode = trim($match[0]);
        } else {
            $episode = '';
        }
        $m3u_title = "#EXTINF:-1, {$item['title']} $episode\r\n";
        $path = str_replace($cfg['playlocal_root_path'], '', $item['path']);
        if (isMobile() || isLinux()) {
            $m3u_path = $cfg['playlocal_share_linux_path'] . $path . "\r\n";
        } else {
            $m3u_path = $cfg['playlocal_share_windows_path'] . $path . "\r\n";
        }
        $m3u_playlist .= $m3u_title . $m3u_path;
    }
    return $m3u_playlist;
}
