<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function scanAppMedia() {
    /*
      global $db, $cfg;

      echo "\n [out] Cheking media files in " . $cfg['TORRENT_FINISH_PATH'];
      $files = [];
      $files = scandir_r($cfg['TORRENT_FINISH_PATH']);

      if ($files === false) {
      return false;
      }

     */
}

function transmission_scan() {
    global $db, $trans, $cfg;


    $transfers = $trans->getAll();

    $transmission_db = $db->getTableData('transmission');

    if ($cfg['MOVE_ONLY_INAPP'] && empty($transmission_db)) {
        echo "\n No Torrents (INAPP set)";
        return false;
    }

    $tors = getRightTorrents($transfers, $transmission_db);

    if ($tors == false) {
        echo "\n No valid torrents downloads found";
        return false;
    }

//var_dump($tors);

    foreach ($tors as $tor) {
        $item = [];

        $item['tid'] = $tor['id'];
        $item['dirname'] = $tor['name'];
        $item['title'] = getFileTitle($item['dirname']);
        $item['status'] = $tor['status'];
        $item['media_type'] = getMediaType($item['dirname']);
        isset($tor['wanted_id']) ? $item['wanted_id'] = $tor['wanted_id'] : null;

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
            echo "\n Movie detected begin moving.. " . $item['title'];
            moveMovie($item, $trans);
        } else if ($item['media_type'] == 'shows') {
            echo "\n Show detected begin moving... " . $item['title'] . ' S' . $item['season'] . 'E' . $item['episode'];
            moveShow($item, $trans);
        }
    }
}

function getRightTorrents($transfers, $transmission_db) {
    global $cfg, $db;

    $finished_list = [];
    $seeding_list = [];


    foreach ($transfers as $transfer) {
        if ($transfer['status'] == 0 && $transfer['percentDone'] == 1) {
            $wanted_id = '';
            //aprovechamos para actualizar wanted
            foreach ($transmission_db as $item) {
                if (isset($item['id']) == $transfer['id']) {
                    $wanted_id = $item['wanted_id'];
                    $wanted_item = $db->getItemById('wanted', $item['wanted_id']);
                    if ($wanted_item['wanted_state'] != 9 && $wanted_item['wanted_state'] != 3) {
                        $update_ary['wanted_state'] = 3;
                        $db->updateRecordById('wanted', $item['wanted_id'], $update_ary);
                    }
                }
            }
            !empty($wanted_id) ? $transfer['wanted_id'] = $wanted_id : null;
            $finished_list[] = array_merge($finished_list, $transfer);
        } else if ($transfer['status'] == 6 && $transfer['percentDone'] == 1) {
            $wanted_id = '';
            foreach ($transmission_db as $item) {
                if (isset($item['id']) == $transfer['id']) {
                    $wanted_id = $item['wanted_id'];
                    $update_ary['wanted_state'] = 2;
                    $db->updateRecordById('wanted', $item['wanted_id'], $update_ary);
                }
            }
            $seeding_list[] = $transfer;
        }
    }

//var_dump($transfers);
//var_dump($finished_list);

    $tors = [];

    if (count($finished_list) >= 1) {
        if (!$cfg['MOVE_ONLY_INAPP']) {
            foreach ($finished_list as $finished) {
                foreach ($transmission_db as $torrent_db) {
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
                // Added to trans/delete need check if works $db->deleteByFieldMatch('transmission', 'tid', $item['tid']);
                (!empty($cfg['FILES_USERGROUP'])) ? chgrp($dest_path, $cfg['FILES_USERGROUP']) : null;
                (!empty($cfg['FILES_PERMS'])) ? chmod($dest_path, $cfg['FILES_PERMS']) : null;
                $ids[] = $item['tid'];
                $trans->delete($ids);
                if (isset($item['wanted_id'])) {
                    $wanted_item = $db->getItemById('wanted', $item['wanted_id']);
                    if ($wanted_item != false) {
                        $update_ary['wanted_state'] = 9;
                        $db->updateRecordById('wanted', $item['wanted_id'], $update_ary);
                    }
                }
            }
        }
    } else {
        leave("\nNo valid files found on torrent with transmission id: " . $item['tid']);
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
                // Added to trans/delete need check if works  $db->deleteByFieldMatch('transmission', 'tid', $item['tid']);
                (!empty($cfg['FILES_USERGROUP'])) ? chgrp($dest_path, $cfg['FILES_USERGROUP']) : null;
                (!empty($cfg['FILES_PERMS'])) ? chmod($dest_path, $cfg['FILES_PERMS']) : null;
                $ids[] = $item['tid'];
                $trans->delete($ids);
                if (isset($item['wanted_id'])) {
                    $wanted_item = $db->getItemById('wanted', $item['wanted_id']);
                    if ($wanted_item != false) {
                        $update_ary['wanted_state'] = 9;
                        $db->updateRecordById('wanted', $item['wanted_id'], $update_ary);
                    }
                }
            }
        }
    } else {
        echo "\nNo valid files found on torrent with id: " . $item['tid'];
    }
}

function wanted_work() {
    global $db, $cfg, $LNG;


    $day_of_week = date("w");

    $wanted_list = $db->getTableData('wanted');
    if (empty($wanted_list) || $wanted_list < 1) {
        echo "\n Wanted list empty";
        return false;
    }

    foreach ($wanted_list as $wanted) {
        if (isset($wanted['wanted_state']) && $wanted['wanted_state'] > 0) {
            echo "\n Jumping wanted check by state {$wanted['wanted_state']}";
            continue;
        }
        if ($wanted['day_check'] != 'L_DAY_ALL') {
            if ($LNG[$wanted['day_check']]['n'] != $day_of_week) {
                echo "\n Jumping wanted check by date check, today is not {$LNG[$wanted['day_check']]['name']}";
                continue;
            }
        }

        $last_check = $wanted['last_check'];

        if (!empty($last_check)) {
            $next_check = $last_check + $cfg['WANTED_DAY_DELAY'];
            if ($next_check > time()) {
                $next_check = $next_check - time();
                echo "\n Jumping wanted check by delay, next check in $next_check seconds";
                continue;
            }
        }
        $wanted_id = $wanted['id'];
        $themoviedb_id = $wanted['themoviedb_id'];
        $title = $wanted['title'];
        $media_type = $wanted['media_type'];
        echo "\n Search for : " . $title . '[' . $media_type . ']';
        if ($media_type == 'movies') {
            $results = search_movie_torrents($title, null, true);
            if (!empty($results) && count($results) > 0) {
                $valid_results = wanted_check_flags($results);
            } else {
                echo "\n No results founds for " . $title;
            }
        } else {
            //$episode = 'S' . $wanted['season'] . 'E' . $wanted['episode'];
            $results = search_shows_torrents($title, null, true);
            if (!empty($results) && count($results) > 0) {
                $valid_results = wanted_check_flags($results);
            } else {
                echo "\n No results founds for " . $title;
            }
        }

        if (!empty($valid_results)) {
            $valid_results[0]['themoviedb_id'] = $themoviedb_id;
            $valid_results[0]['media_type'] = $media_type;
            $valid_results[0]['wanted_id'] = $wanted_id;
            if (send_transmission($valid_results)) {
                $update_ary['wanted_state'] = 1;
            }
        }

        $update_ary['last_check'] = time();
        $update_ary['first_check'] = 1;


        $db->updateRecordById('wanted', $wanted_id, $update_ary);

        echo "\n********************************************************************************************************";
    }
}

function wanted_check_flags($results) {
    global $cfg;

    $noignore = [];

    if (count($cfg['TORRENT_IGNORES_PREFS']) > 0) {
        foreach ($results as $result) {
            $ignore_flag = 0;

            foreach ($cfg['TORRENT_IGNORES_PREFS'] as $ignore) {
                if (stripos($result['title'], $ignore)) {
                    $ignore_flag = 1;
                    echo "\n Wanted: Ignored coincidence for item " . $result['title'] . " by ignore key " . $ignore;
                }
            }
            if ($ignore_flag != 1) {
                $noignore[] = $result;
            }
        }
    } else {
        $noignore = $results;
    }

    if (count($cfg['TORRENT_QUALITYS_PREFS']) > 0) {
        foreach ($cfg['TORRENT_QUALITYS_PREFS'] as $quality) {
            $desire_quality = 0;

            foreach ($noignore as $noignore_result) {

                if (stripos($noignore_result['title'], $quality) || $quality == 'ANY') {
                    echo "\n Wanted: Quality coincidence for item " . $noignore_result['title'] . " by quality key " . $quality;
                    $desire_quality = 1;
                    break;
                }
            }

            if ($desire_quality == 1) {
                $valid_results[] = $noignore_result;
                break;
            }
        }
    } else {
        $valid_results = $noignore;
    }

    return !empty($valid_results) ? $valid_results : false;
}

function send_transmission($results) {
    global $db, $trans;

    //var_dump($results);
    foreach ($results as $result) {

        $trans_db = [];

        $d_link = $result['download'];
        $trans_response = $trans->addUrl(rawurldecode($d_link));
        foreach ($trans_response as $rkey => $rval) {
            $trans_db[0][$rkey] = $rval;
        }
        $trans_db[0]['tid'] = $trans_db[0]['id'];
        $trans_db[0]['status'] = -1;
        $trans_db[0]['profile'] = 0;
        if (!empty($result['themoviedb_id'])) {
            $trans_db[0]['themoviedb_id'] = $result['themoviedb_id'];
        }
        if (!empty($result['wanted_id'])) {
            $trans_db[0]['wanted_id'] = $result['wanted_id'];
        }

        if (!empty($result['media_type'])) {
            $trans_db[0]['media_type'] = $result['media_type'];
        }

        $db->addUniqElements('transmission', $trans_db, 'tid');
    }
    return true;
}

function leave($msg = false) {
    echo "\n Exit Called";
    !empty($msg) ? print $msg . "\n" : print "\n";

    exit();
}
