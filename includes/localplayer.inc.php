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

    $title = getFileTitle($item_requested['file_name']);
    $m3u_title = "#EXTINF:-1, $title\r\n";
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
        $title = getFileTitle($item['file_name']);
        $m3u_title = "#EXTINF:-1, $title $episode\r\n";
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

function get_pl_next_media($master_item, $media_type) {
    global $cfg, $user, $db;

    $library = 'library_' . $media_type;

    $where_view_media = [
        'uid' => ['value' => $user->getId()],
        'media_type' => ['value' => $media_type],
        'themoviedb_id' => ['value' => $master_item['themoviedb_id']],
    ];

    $results = $db->select('view_media', '*', $where_view_media);
    $view_media = $db->fetchAll($results);
    $media_items = $db->getItemsByField($library, 'master', $master_item['id']);

    if (!valid_array($media_items)) {
        exit();
    }

    if (valid_array($view_media)) {
        foreach ($media_items as $kitem => $vitem) {
            foreach ($view_media as $view_item) {
                if ($view_item['file_hash'] == $vitem['file_hash']) {
                    unset($media_items[$kitem]);
                    break;
                }
            }
        }
    }

    if ($media_type == 'shows') {
        usort($media_items, function ($a, $b) {
            $season = $a['season'] - $b['season'];
            if ($season === 0) {
                return $a['episode'] - $b['episode'];
            }
            return $season;
        });
    } else {
        usort($media_items, function ($a, $b) {
            return strcmp($b['title'], $a['title']);
        });
    }

    $m3u_playlist = '';
    foreach ($media_items as $item) {
        $episode = '';
        if ($media_type == 'shows') {
            $match = [];
            if (preg_match('/S\d{2}E\d{2}/i', $item['file_name'], $match) == 1) {
                $episode = trim($match[0]);
            } else {
                $episode = '';
            }
        }
        $title = getFileTitle($item['file_name']);
        $m3u_title = "#EXTINF:-1, $title $episode\r\n";
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
