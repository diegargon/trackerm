<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
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
                    die('Failed to create folders...');
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
        echo "\nNo valid files found on torrent with id:" . $item['tid'];
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
            //exec($unrar);
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
                die('Failed to create folders...');
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
        echo "\nNo valid files found on torrent with id:" . $item['tid'];
    }
}
