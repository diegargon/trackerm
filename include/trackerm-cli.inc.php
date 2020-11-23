<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function outAppMedia() {

}

function inAppMedia() {
    global $db, $trans;

    $transfers = $trans->getAll();

    $torrents_db = $db->getTableData('transmission');

    if (empty($torrents_db)) {
        echo "\n INAPPI:1 No Torrents ";
        leave();
    }


    $tors = getRightTorrents($transfers, $torrents_db);

    if ($tors == false) {
        echo "\n No valid torrents found";
        leave();
    }

//var_dump($tors);

    foreach ($tors as $tor) {
        $item = [];

        $item['tid'] = $tor['id'];
        $item['dirname'] = $tor['name'];
        $item['title'] = getFileTitle($item['dirname']);
        $item['status'] = $tor['status'];
        $item['media_type'] = getMediaType($item['dirname']);
        if ($item['media_type'] == 'shows') {
            $SE = getFileEpisode($item['dirname']);
            (strlen($SE['season']) == 1) ? $item['season'] = 0 . $SE['season'] : $item['season'] = $SE['season'];
            (strlen($SE['chapter']) == 1) ? $item['episode'] = 0 . $SE['chapter'] : $item['episode'] = $SE['chapter'];
        } else {
            $item['season'] = '';
            $item['episode'] = '';
        }
        //echo "\n" . $item['tid'] . ':' . $item['status'] . ':' . $item['title'] . ':' . $item['media_type'] . ':S' . $item['season'] . 'E' . $item['episode'] . "\n";

        if ($item['media_type'] == 'movies') {
            echo "\n Movie detected begin moving" . $item['title'];
            moveMovie($item, $trans);
        } else if ($item['media_type'] == 'shows') {
            echo "\n Show detected begin moving" . $item['title'] . ' S' . $item['season'] . 'E' . $item['episode'];
            moveShow($item, $trans);
        }
    }
}

function getRightTorrents($transfers, $torrents_db) {
    global $cfg;

    $finished_list = [];
    $seeding_list = [];


    foreach ($transfers as $transfer) {
        if ($transfer['status'] == 0 && $transfer['percentDone'] == 1) {
            $finished_list[] = array_merge($finished_list, $transfer);
        } else if ($transfer['status'] == 6 && $transfer['percentDone'] == 1) {
            $seeding_list[] = $transfer;
        }
    }

//var_dump($transfers);
//var_dump($finished_list);

    $tors = [];

    if (count($finished_list) >= 1) {
        if (!$cfg['MOVE_ONLY_INAPP']) {
            foreach ($finished_list as $finished) {
                foreach ($torrents_db as $torrent_db) {
                    if ($torrent_db['tid'] == $finished['id']) {
                        $tors[] = array_merge($tors, $finished);
                    }
                }
            }
        } else {
            $tors = $finished_list;
        }
    }


    return (count($tors) > 0) ? $tors : false;
}

function moveMovie($item, $trans) {
    global $cfg, $db;

    $orig_path = $cfg['TORRENT_FINISH_PATH'] . '/' . $item['dirname'];
    $files_dir = scandir_r($orig_path);

    foreach ($files_dir as $file) {
        $ext_check = substr($file, -3);
        if ($ext_check == 'rar' || $ext_check == 'RAR') {
            $unrar = 'unrar x -y "' . $file . '" "' . dirname($file) . '"';
            echo "\nNeed unrar $file";
            exec($unrar);
            //echo $unrar;
            break;
        }
    }

    isset($unrar) ? $files_dir = scandir_r($orig_path) : false;

    $valid_files = [];

    foreach ($files_dir as $file) {
        if (preg_match($cfg['TORRENT_MEDIA_REGEX'], $file)) {
            $valid_files[] = $file;
        }
    }

    if (count($valid_files) >= 1) {
        $i = 0;

        if ($cfg['CREATE_MOVIE_FOLDERS']) {
            $dest_path = $cfg['MOVIES_PATH'] . '/' . ucwords($item['title']);
            if (!file_exists($dest_path)) {
                umask(0);
                if (!mkdir($dest_path, 0774, true)) {
                    leave('Failed to create folders... ' . $dest_path);
                }
                (!empty($cfg['FILES_USERGROUP'])) ? chgrp($dest_path, $cfg['FILES_USERGROUP']) : null;
            }
        } else {
            $dest_path = $cfg['MOVIES_PATH'];
        }
        foreach ($valid_files as $valid_file) {
            $many = '';
            $file_tags = getFileTags($valid_file);
            $ext = substr($valid_file, -4);
            if ($i > 0) {
                $many = '[' . $i . ']';
            }
            $new_file_name = ucwords($item['title']) . ' ' . $file_tags . $many . $ext;
            $dest_path = $dest_path . '/' . $new_file_name;
            $i++;
            if (rename($valid_file, $dest_path)) {
                $db->deleteByFieldMatch('transmission', 'tid', $item['tid']);
                (!empty($cfg['FILES_USERGROUP'])) ? chgrp($dest_path, $cfg['FILES_USERGROUP']) : null;
                (!empty($cfg['FILES_PERMS'])) ? chmod($dest_path, $cfg['FILES_PERMS']) : null;
                $ids[] = $item['tid'];
                $trans->deleteIds($ids);
            }
        }
    } else {
        leave("\nNo valid files found on torrent with transmission id:" . $item['tid']);
    }
}

function moveShow($item, $trans) {
    global $cfg, $db, $LNG;

    $orig_path = $cfg['TORRENT_FINISH_PATH'] . '/' . $item['dirname'];

    $files_dir = scandir_r($orig_path);

    foreach ($files_dir as $file) {
        $ext_check = substr($file, -3);
        if ($ext_check == 'rar' || $ext_check == 'RAR') {
            $unrar = 'unrar x -y "' . $file . '" "' . dirname($file) . '"';
            echo "\nNeed unrar $file";
            exec($unrar);
            //echo "\n" . $unrar;
            break;
        }
    }

    isset($unrar) ? $files_dir = scandir_r($orig_path) : false;

    $valid_files = [];

    foreach ($files_dir as $file) {
        if (preg_match($cfg['TORRENT_MEDIA_REGEX'], $file)) {
            $valid_files[] = $file;
        }
    }

    if (count($valid_files) >= 1) {
        $i = 0;

        if ($cfg['CREATE_SHOWS_SEASON_FOLDER'] && !empty($item['season'])) {
            $dest_path = $cfg['SHOWS_PATH'] . '/' . ucwords($item['title'] . '/' . $LNG['L_SEASON'] . ' ' . (int) $item['season']);
            $dest_path_father = $cfg['SHOWS_PATH'] . '/' . ucwords($item['title']);
        } else {
            $dest_path = $cfg['SHOWS_PATH'] . '/' . ucwords($item['title']);
        }

        if (!file_exists($dest_path)) {
            umask(0);
            if (!mkdir($dest_path, 0774, true)) {
                leave('Failed to create folders... ' . $dest_path);
            }
            if (!empty($cfg['FILES_USERGROUP'])) {
                chgrp($dest_path, $cfg['FILES_USERGROUP']);
                isset($dest_path_father) ? chgrp($dest_path_father, $cfg['FILES_USERGROUP']) : null;
            }
        }

        foreach ($valid_files as $valid_file) {
            $many = '';
            $file_tags = getFileTags($valid_file);
            $ext = substr($valid_file, -4);
            if ($i > 0) {
                $many = '[' . $i . ']';
            }
            $episode = '';

            if (isset($item['season'])) {
                $episode .= 'S' . $item['season'];
            } else {
                $episode .= 'Sx';
            }

            if (isset($item['episode'])) {
                $episode .= 'E' . $item['episode'];
            } else {
                $episode .= 'Ex';
            }

            $new_file_name = ucwords($item['title']) . ' ' . $episode . ' ' . $file_tags . $many . $ext;
            $dest_path = $dest_path . '/' . $new_file_name;
            $i++;

            if (rename($valid_file, $dest_path)) {
                $db->deleteByFieldMatch('transmission', 'tid', $item['tid']);
                (!empty($cfg['FILES_USERGROUP'])) ? chgrp($dest_path, $cfg['FILES_USERGROUP']) : null;
                (!empty($cfg['FILES_PERMS'])) ? chmod($dest_path, $cfg['FILES_PERMS']) : null;
                $ids[] = $item['tid'];
                $trans->deleteIds($ids);
            }
        }
    } else {
        leave("\nNo valid files found on torrent with id:" . $item['tid']);
    }
}

function wanted_work() {
    /*
     */
}

function leave($msg = false) {
    echo "\n Exit Called";
    !empty($msg) ? print $msg . "\n" : print "\n";

    exit();
}
