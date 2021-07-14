<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function transmission_scan() {
    global $log;

    $tors = getRightTorrents();

    if ($tors == false || (empty($tors['finished']) && empty($tors['seeding']))) {
        $log->debug('Not found any finished or seeding torrent');
        return false;
    }
    // FINISHED TORRENTS
    if (!empty($tors['finished'])) {
        $log->debug('Found torrents finished: ' . count($tors['finished']));
        foreach ($tors['finished'] as $tor) {
            $item = [];

            $item['tid'] = $tor['id'];
            $item['hashString'] = $tor['hashString'];
            $item['files_location'] = $tor['name'];
            $item['files'] = $tor['files'];
            $item['title'] = getFileTitle($tor['name']);
            $item['status'] = $tor['status'];
            if (!empty($tor['media_type'])) {
                $item['media_type'] = $tor['media_type'];
            } else {
                $item['media_type'] = getMediaType($tor['name']);
            }
            !empty($tor['season']) ? $item['season'] = $tor['season'] : null;
            !empty($tor['episode']) ? $item['episode'] = $tor['episode'] : null;
            isset($tor['wanted_id']) ? $item['wanted_id'] = $tor['wanted_id'] : null;
            if ($item['media_type'] == 'movies') {
                $log->debug('Movie stopped detected: Working on ' . $item['title']);
                MovieJob($item);
            } else if ($item['media_type'] == 'shows') {
                $log->debug('Show stopped detected: Working on ' . $item['title']);
                ShowJob($item);
            }
        }
    }
    // SEEDING TORRENTS
    if (!empty($tors['seeding'])) {
        $log->debug('Found torrents seeding: ' . count($tors['seeding']));
        foreach ($tors['seeding'] as $tor) {
            $item = [];

            $item['tid'] = $tor['id'];
            $item['hashString'] = $tor['hashString'];
            $item['files_location'] = $tor['name'];
            $item['files'] = $tor['files'];
            $item['title'] = getFileTitle($tor['name']);
            $item['status'] = $tor['status'];
            if (!empty($tor['media_type'])) {
                $item['media_type'] = $tor['media_type'];
            } else {
                $item['media_type'] = getMediaType($tor['name']);
            }
            !empty($tor['season']) ? $item['season'] = $tor['season'] : null;
            !empty($tor['episode']) ? $item['episode'] = $tor['episode'] : null;
            isset($tor['wanted_id']) ? $item['wanted_id'] = $tor['wanted_id'] : null;

            if ($item['media_type'] == 'movies') {
                $log->debug('Movie seeding detected: Checking ' . $item['title']);
                MovieJob($item, true);
            } else if ($item['media_type'] == 'shows') {
                $log->debug('Show seeding detected: Checking ' . $item['title']);
                ShowJob($item, true);
            }
        }
    }
}

function getRightTorrents() {
    global $cfg, $log, $trans, $db;

    $finished_list = [];
    $seeding_list = [];

    !empty($trans) ? $transfers = $trans->getAll() : null;
    if (empty($transfers)) {
        return false;
    }
    $wanted_db = $db->getTableData('wanted');

    if ($cfg['move_only_inapp'] && empty($wanted_db)) {
        $log->debug('No Torrents (INAPP set)');
        return false;
    }

    foreach ($transfers as $transfer) {
        if ($transfer['status'] == 0 && $transfer['percentDone'] == 1) {
            $finished_list[] = $transfer;
        } else if ($transfer['status'] == 6 && $transfer['percentDone'] == 1) {
            $seeding_list[] = $transfer;
        }
    }

    $tors = [];
    // FINISHED TORS
    if (count($finished_list) >= 1) {
        foreach ($finished_list as $finished) {
            $add_tor = 0;
            foreach ($wanted_db as $wanted_item) {
                if ($wanted_item['hashString'] == $finished['hashString']) {
                    !empty($wanted_item['media_type']) ? $finished['media_type'] = $wanted_item['media_type'] : null;
                    !empty($wanted_item['season']) ? $finished['season'] = $wanted_item['season'] : null;
                    !empty($wanted_item['episode']) ? $finished['episode'] = $wanted_item['episode'] : null;
                    $add_tor = 1;
                    break;
                }
            }
            if (!$cfg['move_only_inapp'] || $add_tor) {
                $tors['finished'][] = $finished;
            }
        }
    }

    //SEEDING TORS
    if (count($seeding_list) >= 1) {
        foreach ($seeding_list as $seeding) {
            $add_tor = 0;
            foreach ($wanted_db as $wanted_item) {
                if ($wanted_item['hashString'] == $seeding['hashString']) {
                    !empty($wanted_item['media_type']) ? $seeding['media_type'] = $wanted_item['media_type'] : null;
                    !empty($wanted_item['season']) ? $seeding['season'] = $wanted_item['season'] : null;
                    !empty($wanted_item['episode']) ? $seeding['episode'] = $wanted_item['episode'] : null;
                    $add_tor = 1;
                    break;
                }
            }
            if (!$cfg['move_only_inapp'] || $add_tor) {
                $tors['seeding'][] = $seeding;
            }
        }
    }

    return $tors;
}

function MovieJob($item, $linked = false) {
    global $cfg, $log, $trans, $db, $LNG;

    $valid_files = [];
    $valid_files = get_valid_files($item);

    if ($valid_files && count($valid_files) >= 1) {
        if ($cfg['create_movie_folders']) {
            $folder_title = preg_replace('/\s+/', ' ', $item['title']);
            if (is_array($cfg['MOVIES_PATH'])) {
                $dest_path = $cfg['MOVIES_PATH'][0] . '/' . ucwords($folder_title);
            } else {
                $dest_path = $cfg['MOVIES_PATH'] . '/' . ucwords($folder_title);
            }
            if (!file_exists($dest_path)) {
                umask(0);
                if (!mkdir($dest_path, octdec("0" . $cfg['dir_perms']), true)) {
                    leave('Failed to create folders... ' . $dest_path);
                }
                (!empty($cfg['files_usergroup'])) ? chgrp($dest_path, $cfg['files_usergroup']) : null;
            }
        } else {
            if (!is_array($cfg['MOVIES_PATH'])) {
                $dest_path = $cfg['MOVIES_PATH'];
            } else {
                $dest_path = $cfg['MOVIES_PATH'][0];
            }
        }

        $i = 1;
        $new_media = '';
        $num_valid = count($valid_files);
        $num_valid > 1 ? sort($valid_files) : null;

        foreach ($valid_files as $valid_file) {

            $file_tags = getFileTags($valid_file);
            $ext = substr($valid_file, -4);

            if ($num_valid > 1) {
                $title = ucwords(getFileTitle(basename($valid_file)));
            } else {
                $title = ucwords($item['title']);
            }
            $title = preg_replace('/\s+/', ' ', $title);
            !empty($file_tags) ? $file_tags = ' ' . $file_tags : null;
            if ($num_valid > 1) {
                $numerated = '[' . $i . ']';
            } else {
                $numerated = '';
            }
            $new_file_name = $title . $file_tags . $numerated . $ext;
            $i++;
            $final_dest_path = $dest_path . '/' . $new_file_name;

            if (file_exists($final_dest_path) && $linked) {
                continue;
            }

            if (!$linked) {
                $log->debug('Moved work: ' . $item['hashString']);
                if (move_media($valid_file, $final_dest_path) && ($valid_file == end($valid_files) )) {
                    $log->debug("Cleaning torrent id/hash:  {$item['tid']} : {$item['hashString']}");
                    $hashes[] = $item['hashString'];
                    file_exists(dirname($valid_file) . '/trackerm-unrar') ? unlink(dirname($valid_file) . '/trackerm-unrar') : null;
                    file_exists(dirname($valid_file) . '.unrar') ? unlink(dirname($valid_file) . '.unrar') : null;

                    $wanted_item = $db->getItemByField('wanted', 'hashString', $item['hashString']);
                    if (!empty($wanted_item)) {
                        if (!empty($cfg['autoclean_moved_wanted'])) {
                            $log->debug('Removing wanted id by move: ' . $wanted_item['id']);
                            $db->deleteItemById('wanted', $wanted_item['id']);
                        } else {
                            $log->debug('Setting to moved wanted id: ' . $wanted_item['id']);
                            $update_ary['wanted_status'] = 9;
                            $update_ary['id'] = $wanted_item['id'];
                            $db->updateItemByField('wanted', $update_ary, 'id');
                        }
                    }

                    $trans->deleteHashes($hashes);
                    $work_path = dirname($valid_file);
                    file_exists($work_path) && ($work_path != $cfg['TORRENT_FINISH_PATH']) && (end($valid_files) == $valid_file) ? @rmdir($work_path) : null;
                }
            } else {
                $log->debug("Link Seeding: {$item['tid']} : {$item['hashString']}");
                linking_media($valid_file, $final_dest_path);
                $new_media .= basename($final_dest_path) . "\n";
            }
        }
        !empty($new_media) ? notify_mail(['subject' => $LNG['L_NEW_MEDIA_AVAILABLE'], 'msg' => $new_media]) : null;
    } else {
        $log->info('No valid files found on torrent with transmission id:' . "{$item['tid']} : {$item['hashString']} ");
    }
}

function ShowJob($item, $linked = false) {
    global $cfg, $db, $LNG, $trans, $log;

    $valid_files = [];
    $valid_files = get_valid_files($item);

    if ($valid_files && count($valid_files) >= 1) {
        $i = 1;
        $new_media = '';

        foreach ($valid_files as $valid_file) {
            $many = '';
            $file_tags = getFileTags($valid_file);
            $ext = substr($valid_file, -4);

            if (count($valid_files) == 1) {
                if (!empty($item['season']) && !empty($item['episode'])) {
                    $SE['season'] = $item['season'];
                    $SE['episode'] = $item['episode'];
                } else {
                    $SE = getFileEpisode($item['files_location']);
                }
            } else {
                $SE = getFileEpisode(basename($valid_file));
            }

            if (isset($SE['season']) && isset($SE['episode']) && !empty($SE['season'] && !empty($SE['episode']))) {
                (strlen($SE['season']) == 1) ? $_season = 0 . $SE['season'] : $_season = $SE['season'];
                (strlen($SE['episode']) == 1) ? $_episode = 0 . $SE['episode'] : $_episode = $SE['episode'];
            } else {
                $_season = 'xx';
                $_episode = 'xx';
            }

            $episode = '';
            $episode .= 'S' . $_season;
            $episode .= 'E' . $_episode;
            //END EPISODE NAME
            //CREATE PATHS
            $title = getFileTitle($item['title']);
            $title = preg_replace('!\s+!', ' ', $title);
            if ($cfg['create_shows_season_folder'] && !empty($_season)) {
                ($_season != "xx") ? $_season = (int) $_season : null; // 01 to 1 for directory
                if (is_array($cfg['SHOWS_PATH'])) {
                    $dest_path = $cfg['SHOWS_PATH'][0] . '/' . ucwords($title . '/' . $LNG['L_SEASON'] . ' ' . $_season);
                    $dest_path_father = $cfg['SHOWS_PATH'][0] . '/' . ucwords($title);
                } else {
                    $dest_path = $cfg['SHOWS_PATH'] . '/' . ucwords($title . '/' . $LNG['L_SEASON'] . ' ' . $_season);
                    $dest_path_father = $cfg['SHOWS_PATH'] . '/' . ucwords($title);
                }
            } else {
                if (is_array($cfg['SHOWS_PATH'])) {
                    $dest_path = $cfg['SHOWS_PATH'][0] . '/' . ucwords($title);
                } else {
                    $dest_path = $cfg['SHOWS_PATH'] . '/' . ucwords($title);
                }
            }
            //END CREATE PATHS
            //CREATE FOLDERS
            if (!file_exists($dest_path)) {
                umask(0);
                if (!mkdir($dest_path, octdec("0" . $cfg['dir_perms']), true)) {
                    leave('Failed to create folders... ' . $dest_path);
                }
                if (!empty($cfg['files_usergroup'])) {
                    chgrp($dest_path, $cfg['files_usergroup']);
                    isset($dest_path_father) ? chgrp($dest_path_father, $cfg['files_usergroup']) : null;
                }
            }
            //END CREATE FOLDERS

            $new_file_name = ucwords($title) . ' ' . $episode . ' ' . $file_tags . $ext;
            $final_dest_path = $dest_path . '/' . $new_file_name;

            if (file_exists($final_dest_path) && !$linked && !is_link($final_dest_path)) {
                $many = '[' . $i . ']';
                $new_file_name = ucwords($title) . ' ' . $episode . ' ' . $file_tags . $many . $ext;
                $final_dest_path = $dest_path . '/' . $new_file_name;
                $i++;
            } else if (file_exists($final_dest_path) && $linked) {
                continue;
            }

            if (!$linked) {
                $log->debug("Moved work: {$item['tid']} : {$item['hashString']}");
                if (move_media($valid_file, $final_dest_path) && ($valid_file == end($valid_files) )) {
                    $log->debug("Cleaning torrent: {$item['tid']} : {$item['hashString']}");
                    $hashes[] = $item['hashString'];
                    file_exists(dirname($valid_file) . '/trackerm-unrar') ? unlink(dirname($valid_file) . '/trackerm-unrar') : null;
                    file_exists(dirname($valid_file) . '.rar.unrar') ? unlink(dirname($valid_file) . '.rar.unrar') : null;

                    $wanted_item = $db->getItemByField('wanted', 'hashString', $item['hashString']);
                    if (!empty($wanted_item)) {
                        if (!empty($cfg['autoclean_moved_wanted'])) {
                            $log->debug('Removing wanted id by move: ' . $wanted_item['id']);
                            $db->deleteItemById('wanted', $wanted_item['id']);
                        } else {

                            $log->debug("Setting to moved wanted {$item['tid']} : {$item['hashString']}");
                            $update_ary['wanted_status'] = 9;
                            $update_ary['id'] = $wanted_item['id'];
                            $db->updateItemByField('wanted', $update_ary, 'id');
                        }
                    }
                    $trans->deleteHashes($hashes);
                    $work_path = dirname($valid_file);
                    file_exists($work_path) && ($work_path != $cfg['TORRENT_FINISH_PATH']) && (end($valid_files) == $valid_file) ? @rmdir($work_path) : null;
                }
            } else {
                $log->debug("Link Seeding: {$item['tid']} : {$item['hashString']}");
                linking_media($valid_file, $final_dest_path);
                $new_media .= basename($final_dest_path) . "\n";
            }
        }
        !empty($new_media) ? notify_mail(['subject' => $LNG['L_NEW_MEDIA_AVAILABLE'], 'msg' => $new_media]) : null;
    } else {
        $log->info('No valid files found on torrent with ' . "{$item['tid']} : {$item['hashString']}");
    }
}

function get_valid_files($item) {
    global $cfg, $LNG, $log;

    //FIX: if two file need unrar and is not in his own directory the unrar flag is setting on the root then will detect both unrar
    $orig_path = $cfg['TORRENT_FINISH_PATH'] . '/' . $item['files_location'];

    if (is_dir($orig_path)) {

        $files_dir = scandir_r($orig_path);

        foreach ($files_dir as $file) {
            $ext_check = substr($file, -3);

            if (strtolower($ext_check) == 'rar') {
                if (file_exists($cfg['unrar_path'])) {
                    $unrar_check = dirname($file) . '/trackerm-unrar';
                    if (!file_exists($unrar_check)) {
                        if (check_file_encrypt('rar', $file)) {
                            $log->addStateMsg("[{$LNG['L_ERROR']}] {$LNG['L_ERR_FILE_ENCRYPT_MANUAL']} ($file)");
                            notify_mail(['subject' => $LNG['L_ERR_FILE_ENCRYPT_MANUAL'], 'msg' => $file]);
                            // we continue and try since the function need test and TODO.
                        }
                        touch($unrar_check);
                        !empty($cfg['files_usergroup']) ? chgrp($unrar_check, $cfg['files_usergroup']) : null;
                        $unrar = $cfg['unrar_path'] . ' x -p- -y "' . $file . '" "' . dirname($file) . '"';
                        $log->info('Need unrar' . $file);
                        exec($unrar);
                        break;
                    } else {
                        $log->debug('Unrar flag is set skipping');
                        break;
                    }
                } else {
                    $log->addStateMsg('[' . $LNG['L_NOTE'] . '] ' . $LNG['L_NEED_UNRAR']);
                }
            }
        }

        isset($unrar) ? $files_dir = scandir_r($orig_path) : false;

        $valid_files = [];

        foreach ($files_dir as $file) {
            if (is_media_file($file)) {
                $valid_files[] = $file;
            }
        }
    } else {
        $ext_check = substr($item['files_location'], -3);
        $work_path = $cfg['TORRENT_FINISH_PATH'] . '/' . substr($item['files_location'], 0, -4);
        if (strtolower($ext_check) == 'rar') {
            if (!file_exists($work_path)) {
                mkdir($work_path);
            }
            if (file_exists($cfg['unrar_path'])) {
                $unrar_check = $orig_path . '.unrar';
                if (!file_exists($unrar_check)) {
                    if (check_file_encrypt('rar', $file)) {
                        $log->addStateMsg("[{$LNG['L_ERROR']}]{$LNG['L_ERR_FILE_ENCRYPT_MANUAL']} ($file)");
                        notify_mail(['subject' => $LNG['L_ERR_FILE_ENCRYPT_MANUAL'], 'msg' => $file]);
                        // we continue and try since the function need test and TODO.
                    }
                    $unrar = $cfg['unrar_path'] . ' e -p- -y "' . $orig_path . '" "' . $work_path . '"';
                    exec($unrar);
                    touch($unrar_check);
                }

                $files_dir = scandir_r($work_path);

                if (empty($files_dir)) {
                    $log->debug('Work path is empty');
                }
                foreach ($files_dir as $file) {
                    if (is_media_file($file)) {
                        $valid_files[] = $file;
                    } else {
                        if (!is_dir($file)) {
                            unlink($file);
                        }
                    }
                }
            }
        } else {
            foreach ($item['files'] as $file) {
                if (is_media_file($file['name'])) {
                    $file_full_path = $cfg['TORRENT_FINISH_PATH'] . '/' . $file['name'];
                    $valid_files[] = $file_full_path;
                }
            }
        }
    }

    return !empty($valid_files) ? $valid_files : false;
}

function move_media($valid_file, $final_dest_path) {
    global $cfg, $log, $LNG;

    if (empty($valid_file) || empty($final_dest_path)) {
        return false;
    }

    if (move_file($valid_file, $final_dest_path)) {
        if (!empty($cfg['files_usergroup'])) {
            if (!chgrp($final_dest_path, $cfg['files_usergroup'])) {
                $log->err("chgrp on $valid_file fail (move_media)");
                return false;
            }
        }
        umask(0);
        if (!empty($cfg['files_perms'])) {
            if (!chmod($final_dest_path, octdec("0" . $cfg['files_perms']))) {
                $log->err("chmod on $valid_file fail (move_media)");
                return false;
            }
        }
        $log->info("Rename sucessful: $valid_file : $final_dest_path");
        $log->addStateMsg('[' . $LNG['L_MOVED'] . '] ' . basename($final_dest_path));
        return true;
    }

    $log->err("Move failed: $valid_file : $final_dest_path");
    return false;
}

function linking_media($valid_file, $final_dest_path) {
    global $cfg, $log, $LNG;

    if (symlink($valid_file, $final_dest_path)) {
        if (!empty($cfg['files_usergroup'])) {
            if (file_exists($valid_file)) {
                if (!chgrp($valid_file, $cfg['files_usergroup'])) {
                    $log->err("chgrp on $valid_file fail (linking)");
                    return false;
                }
            } else {
                return false;
            }
        }
        umask(0);
        if (!empty($cfg['files_perms'])) {
            if (!chmod($valid_file, octdec("0" . $cfg['files_perms']))) {
                $log->err("chmod on $valid_file fail (linking)");
                return false;
            }
        }
        $log->info("Linking sucessful: $valid_file : $final_dest_path");
        $log->addStateMsg('[' . $LNG['L_LINKED'] . '] ' . basename($final_dest_path));
        return true;
    }

    $log->err("Linking failed: $valid_file : $final_dest_path");
    return false;
}

function wanted_work() {
    global $db, $cfg, $LNG, $log, $trans;

    if (empty($trans)) {
        return false;
    }
    $trans->updateWanted();
    $wanted_list = $db->getTableData('wanted');
    if (!valid_array($wanted_list)) {
        $log->debug('Wanted list empty');
        return false;
    }
    //TODO: Check better the time thing
    $day_of_week = date('N');

    $log_j_direct = '';
    $log_j_state = '';
    $log_j_date_no = '';
    $log_j_date_never = '';
    $log_track_show = '';

    foreach ($wanted_list as $wanted) {
        $valid_results = [];

        if ($wanted['direct'] == 1) {
            (!empty($log_j_direct)) ? $log_j_direct .= ',' : null;
            $log_j_direct .= $wanted['id'];
            continue;
        }
        if (!empty($wanted['track_show'])) {
            $log_track_show .= '[' . $wanted['title'] . ']';
            //TODO check if we can send wanted_list to avoid query again
            tracker_shows($wanted);
            continue;
        }
        if (isset($wanted['wanted_status']) && $wanted['wanted_status'] >= 0) {
            $log_j_state .= '[' . $wanted['title'] . '(' . $trans->getStatusName($wanted['wanted_status']) . ')]';
            continue;
        }
        if ($wanted['day_check'] == -1) {
            $log_j_date_never .= '[' . $wanted['title'] . ']';
            continue;
        }
        if (($wanted['day_check'] > 0) && $wanted['day_check'] != $day_of_week) {
            $log_j_date_no .= '[' . $wanted['title'] . '(' . $LNG[$cfg['CHECK_DAYS'][$wanted['day_check']]] . ')]';
            continue;
        }

        $last_check = $wanted['last_check'];

        if (!empty($last_check)) {
            $next_check = $last_check + $cfg['wanted_day_delay'];
            if ($next_check > time()) {
                $next_check = $next_check - time();
                $log->debug("Jumping wanted {$wanted['title']} check by delay, next check in $next_check seconds");
                continue;
            }
        }
        $wanted_id = $wanted['id'];
        $themoviedb_id = $wanted['themoviedb_id'];
        $title = !empty($wanted['custom_title']) ? $wanted['custom_title'] : $wanted['title'];
        $media_type = $wanted['media_type'];

        if ($media_type == 'movies') {
            $log->debug('Search for : ' . $title . "[ $media_type ]");

            $search['words'] = $title;
            $results = search_media_torrents($media_type, $search);
            if (!empty($results) && count($results) > 0) {
                $results_pass_flags = wanted_check_flags($wanted, $results);
                !empty($results_pass_flags) ? $valid_results = wanted_check_title($title, $results_pass_flags) : null;
            } else {
                $log->debug('No results founds for ' . $title);
            }
        } else {
            (strlen($wanted['season']) == 1) ? $season = '0' . $wanted['season'] : $season = $wanted['season'];
            (strlen($wanted['episode']) == 1) ? $episode = '0' . $wanted['episode'] : $episode = $wanted['episode'];
            $s_episode = 'S' . $season . 'E' . $episode;
            $search['words'] = $title;
            $search['episode'] = $s_episode;
            $log->debug(' Search for : ' . $title . " $s_episode [ $media_type ]");
            $results = search_media_torrents($media_type, $search);
            if (!empty($results) && count($results) > 0) {
                $results_pass_flags = wanted_check_flags($wanted, $results);
                !empty($results_pass_flags) ? $valid_results = wanted_check_title($title, $results_pass_flags) : null;
            } else {
                $log->debug('No results founds for ' . $title . ' ' . $s_episode);
            }
        }

        if (!empty($valid_results)) {
            $valid_results[0]['themoviedb_id'] = $themoviedb_id;
            $valid_results[0]['media_type'] = $media_type;
            $valid_results[0]['wanted_id'] = $wanted_id;
            $first_valid[] = $valid_results[0];
            $log->debug('Sending to transmission ' . $valid_results[0]['title']);
            if (send_transmission($first_valid)) {
                $state_msg = '[' . $LNG['L_DOWNLOADING'] . '] ';
                if (!empty($s_episode)) {
                    $state_msg .= $title . ' ' . $s_episode;
                } else {
                    $state_msg .= $title;
                }
                $log->addStateMsg($state_msg);
            }
        } else {
            $update_ary['last_check'] = time();
            $update_ary['first_check'] = 1;
            $db->updateItemById('wanted', $wanted['id'], $update_ary);
        }

        $log->debug('********************************************************************************************************');
    }
    !empty($log_j_direct) ? $log->debug("Jumping [Direct] " . $log_j_direct) : null;
    !empty($log_track_show) ? $log->debug("Track Show check: " . $log_track_show) : null;
    !empty($log_j_date_never) ? $log->debug("Jumping [Never] " . $log_j_date_never) : null;
    !empty($log_j_date_no) ? $log->debug("Jumping [NotToday] " . $log_j_date_no) : null;
    !empty($log_j_state) ? $log->debug("Jumping [State] " . $log_j_state) : null;
}

function wanted_check_title($title, $results) {
    global $log, $cfg;
    $valid = [];

    $req_title = iconv($cfg['charset'], "ascii//TRANSLIT", trim($title));
    $req_title = clean_title($req_title);
    $req_title = preg_replace('/\s+/', ' ', $req_title);
    foreach ($results as $item) {
        $res_title = iconv($cfg['charset'], "ascii//TRANSLIT", trim(getFileTitle($item['title'])));
        $res_title = clean_title($res_title);
        $res_title = preg_replace('/\s+/', ' ', $res_title);
        if (strcmp(strtolower($res_title), strtolower($req_title)) == 0) {
            $log->debug('Wanted: Valid title found for title: ' . $req_title . ' ->' . $res_title . '[' . $item['title'] . ']');
            $valid[] = $item;
        } else {
            $log->debug('Wanted: Title discarded since is not exact *' . strtolower($req_title) . '*:*' . strtolower($res_title) . '*');
        }
    }

    return (count($valid) > 0) ? $valid : false;
}

function wanted_check_flags($wanted, $results) {
    global $cfg, $log;

    $torrent_ignore_prefs = [];
    $custom_words_ignore = [];
    $custom_words_require = [];
    $final_results = [];

    /*
     * IGNORE KEYWORDS GLOBAL && CUSTOM
     */
    if (!empty($wanted['custom_words_ignore'])) {
        $custom_words_ignore = explode(',', $wanted['custom_words_ignore']);
    }
    if (count($cfg['torrent_ignore_prefs']) > 0) {
        $torrent_ignore_prefs = $cfg['torrent_ignore_prefs'];
    }
    if (count($custom_words_ignore) > 0) {
        $torrent_ignore_prefs = array_merge($custom_words_ignore, $torrent_ignore_prefs);
    }
    if (count($torrent_ignore_prefs) > 0) {
        foreach ($results as $key_result => $result) {
            foreach ($cfg['torrent_ignore_prefs'] as $ignore) {
                if (stripos($result['title'], trim($ignore))) {
                    $log->debug('Wanted: Ignored coincidence for item ' . $result['title'] . ' by ignore key ' . $ignore);
                    unset($results[$key_result]);
                    break;
                }
            }
        }
        if (!valid_array($results)) {
            return false;
        }
    }

    /*
     * If only proper drop all not contains proper
     */
    if (!empty($wanted['only_proper'])) {
        foreach ($results as $key_result => $result) {
            if (!stripos($result['title'], 'PROPER')) {
                unset($results[$key_result]);
            }
        }
        if (!valid_array($results)) {
            return false;
        }
    }

    /*
     *  Require OR prefs A OR B need
     */
    if (!empty($cfg['torrent_require_or_prefs']) && (count($cfg['torrent_require_or_prefs']) > 0) && !empty($results) && (count($results) > 0)) {

        foreach ($results as $key_result => $result) {
            $match = 0;
            $words_checked = '';
            foreach ($cfg['torrent_require_or_prefs'] as $or_prefs) {
                if (strpos($result['title'], $or_prefs)) {
                    $match = 1;
                    break;
                } else {
                    empty($words_checked) ? $words_checked = $or_prefs : $words_checked .= ',' . $or_prefs;
                }
            }
            if ($match === 0) {
                $log->debug('Wanted: Drop item by global OR ' . $result['title'] . ' any required words ' . $words_checked);
                unset($results[$key_result]);
            }
        }
    }

    /*
     *  Customs words require in the title
     */
    if (!empty($wanted['custom_words_require']) && !empty($results) && (count($results) > 0)) {
        $custom_words_require = explode(',', $wanted['custom_words_require']);
        foreach ($results as $key_result => $result) {
            foreach ($custom_words_require as $word_require) {
                if (!(stripos($result['title'], trim($word_require)))) {
                    $log->debug('Wanted: Drop item by custom require words ' . $result['title'] . ' required word ' . $word_require);
                    unset($results[$key_result]);
                }
            }
        }
    }

    /*
     * Custom ignore words
     */
    if (!empty($wanted['custom_words_ignore']) && count($results) > 0) {
        $custom_words_ignore = explode(',', $wanted['custom_words_ignore']);
        foreach ($results as $key_result => $result) {
            foreach ($custom_words_ignore as $word_ignore) {
                if ((stripos($result['title'], $word_ignore))) {
                    $log->debug('Wanted: Drop item by custom ignore words ' . $result['title'] . ' ignore word ' . trim($word_ignore));
                    unset($results[$key_result]);
                }
            }
        }
    }

    if (!valid_array($results)) {
        return false;
    }
    /*
     * Order Priority Quality+Proper -> Quality -> ANY if set
     */

    if (count($cfg['torrent_quality_prefs']) > 0) {
        foreach ($cfg['torrent_quality_prefs'] as $quality) {
            if (strtoupper($quality) == 'ANY') {
                foreach ($results as $key_result => $result) {
                    if (stripos($result['title'], 'PROPER')) {
                        $final_results[] = $result;
                        unset($results[$key_result]);
                    }
                }
                foreach ($results as $key_result => $result) {
                    $final_results[] = $result;
                    unset($results[$key_result]);
                }
            } else {
                foreach ($results as $key_result => $result) {
                    if (stripos($result['title'], $quality) && stripos($result['title'], 'PROPER')) {
                        $final_results[] = $result;
                        unset($results[$key_result]);
                    }
                }
                foreach ($results as $key_result => $result) {
                    if (stripos($result['title'], $quality)) {
                        $final_results[] = $result;
                        unset($results[$key_result]);
                    }
                }
            }
        }
    }


    return valid_array($final_results) ? $final_results : false;
}

function tracker_shows($wanted) {
    global $db, $cfg, $log;

    $from_season = $wanted['season'];
    $from_episode = $wanted['episode'];
    $oid = $wanted['themoviedb_id'];

    if (empty($oid) || empty($from_season) || empty($from_episode)) {
        return false;
    }

    $seasons = [];
    $result = $db->query('SELECT season,episode FROM shows_details WHERE themoviedb_id=' . $oid . '');
    $items = $db->fetchAll($result);

    foreach ($items as $item) {
        (!isset($seasons[$item['season']]) || $seasons[$item['season']] < $item['episode']) ? $seasons[$item['season']] = $item['episode'] : null;
    }

    //Create list of array season/episodes and exclude what not meet the criteria from_season/episode
    $list_episodes = [];
    foreach ($seasons as $season => $episodes) {
        if ($season < $from_season) {
            continue;
        }
        for ($i = 1; $i <= $episodes; $i++) {
            if (!($season == $from_season && $i < $from_episode)) {
                $list_episodes[$season][] = $i;
            }
        }
    }

    //Get actual wanted list (unfinished) for tmdb id, we use later
    $result = $db->query("SELECT * FROM wanted WHERE themoviedb_id = $oid AND media_type = 'shows' AND wanted_status < 5 AND track_show = 0");
    $items_match = $db->fetchAll($result);
    $items_match_count = count($items_match);

    //nocount option// add later to max for ignore max_wanted_track_download
    $nocount = 0;
    foreach ($items_match as $item_match) {
        if ($item_match['ignore_count']) {
            $nocount++;
        }
    }
    //From all the episode that meet the criteria check if already have that item
    //or if already in wanted.
    $have_shows = get_have_shows($oid);
    foreach ($list_episodes as $season => $episodes) {
        foreach ($episodes as $key_episode => $episode) {
            //FIXME: When one episode on track show finish download, wanted state is > 5 but cli not linked the show yet (before track_show is called)
            // neither update library shows. On check "that show not exists" then not drop and try send this chapter again.
            // Next cli run the rescan process already is done and is ok.
            // Fixing calling rebuild show here seems over kill.
            // Get all wanted regarless or wanted_status for drop gump up the max_wanted_track, since > 5 must not count for send next show
            // At this moment the isn't added because duplicate but neither, must wait next cli run when shows library is building and detect as a
            // have_show.
            //FIXME: Despues de descargar Algo 1x05 como 1x05 no esta la BD todavia vuelve añadir 1x05 que no se añade por que esta sirviendo
            //habria que comprobar si Algo 1x05 esta en wanted y si esta eliminarlo para que añada 1x06.

            if (valid_array($have_shows)) {
                foreach ($have_shows as $have_show) {
                    if ($have_show['season'] == $season && $have_show['episode'] == $episode) {
                        unset($list_episodes[$season][$key_episode]);
                        if (count($list_episodes[$season]) == 0) {
                            unset($list_episodes[$season]);
                        }
                        //break;
                    }
                }
            }

            if ($items_match_count > 0) {
                foreach ($items_match as $item_match) {
                    if (($item_match['season'] == $season) && ($item_match['episode'] == $episode)) {
                        unset($list_episodes[$season][$key_episode]);
                        if (count($list_episodes[$season]) == 0) {
                            unset($list_episodes[$season]);
                        }
                        //break;
                    }
                }
            }
        }
    }
    if (!valid_array($list_episodes)) {
        return false;
    }
    $item = mediadb_getFromCache('shows', $oid);
    $title = $item['title'];
    empty($cfg['max_wanted_track_downloads']) ? $max_wanted_track_downloads = 1 + $nocount : $max_wanted_track_downloads = $cfg['max_wanted_track_downloads'] + $nocount;

    $inherint_track = null;
    !empty($wanted['custom_words_ignore']) ? $inherint_track['custom_words_ignore'] = $wanted['custom_words_ignore'] : null;
    !empty($wanted['custom_words_require']) ? $inherint_track['custom_words_require'] = $wanted['custom_words_require'] : null;
    !empty($wanted['custom_title']) ? $inherint_track['custom_title'] = $wanted['custom_title'] : null;
    !empty($wanted['day_check']) ? $inherint_track['day_check'] = $wanted['day_check'] : null;

    foreach ($list_episodes as $season => $episodes) {
        foreach ($episodes as $key_episode => $episode) {
            if ($items_match_count < $max_wanted_track_downloads) {
                $log->info('Sending to wanted:' . "$title $season:$episode");
                wanted_episode($oid, $season, $episode, 0, $inherint_track);
                $items_match_count++;
            } else {
                break;
            }
        }
    }
}

function send_transmission($results) {
    global $db, $cfg, $trans;

    foreach ($results as $result) {

        $trans_db = [];

        $d_link = $result['download'];

        ($cfg['wanted_paused']) ? $trans_opt['paused'] = true : $trans_opt = [];

        !empty($trans) ? $trans_response = $trans->addUrl($d_link, null, $trans_opt) : null;
        if (empty($trans) || empty($trans_response)) {
            return false;
        }

        foreach ($trans_response as $rkey => $rval) {
            $trans_db[0][$rkey] = $rval;
        }
        //UPDATE WANTED.DB
        if ($cfg['wanted_paused']) {
            $update_ary['wanted_status'] = 0;
        } else {
            $update_ary['wanted_status'] = 1;
        }
        $update_ary['tid'] = $trans_db[0]['id'];
        $update_ary['hashString'] = $trans_db[0]['hashString'];
        $update_ary['last_check'] = time();
        $update_ary['first_check'] = 1;

        $db->updateItemById('wanted', $result['wanted_id'], $update_ary);
    }
    return true;
}

function leave($msg = false) {
    global $log;

    $log->debug('Exit Called  ');
    !empty($msg) ? $log->err($msg) : null;

    exit();
}
