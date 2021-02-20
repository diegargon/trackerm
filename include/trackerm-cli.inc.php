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
        $log->debug(" Not found any finished or seeding torrent");
        return false;
    }
    // FINISHED TORRENTS
    if (!empty($tors['finished'])) {
        $log->debug(" Found torrents finished: " . count($tors['finished']));
        foreach ($tors['finished'] as $tor) {
            $item = [];

            $item['tid'] = $tor['id'];
            $item['hashString'] = $tor['hashString'];
            $item['files_location'] = $tor['name'];
            $item['files'] = $tor['files'];
            $item['title'] = getFileTitle($tor['name']);
            $item['status'] = $tor['status'];
            $item['media_type'] = getMediaType($tor['name']);
            isset($tor['wanted_id']) ? $item['wanted_id'] = $tor['wanted_id'] : null;

            if ($item['media_type'] == 'movies') {
                $log->debug(" Movie stopped detected begin working on it.. " . $item['title']);
                MovieJob($item);
            } else if ($item['media_type'] == 'shows') {
                $log->debug(" Show stopped detected begin working on it... " . $item['title']);
                ShowJob($item);
            }
        }
    }
    // SEEDING TORRENTS
    if (!empty($tors['seeding'])) {
        $log->debug(" Found torrents seeding: " . count($tors['seeding']));
        foreach ($tors['seeding'] as $tor) {
            $item = [];

            $item['tid'] = $tor['id'];
            $item['hashString'] = $tor['hashString'];
            $item['files_location'] = $tor['name'];
            $item['files'] = $tor['files'];
            $item['title'] = getFileTitle($tor['name']);
            $item['status'] = $tor['status'];
            $item['media_type'] = getMediaType($tor['name']);
            isset($tor['wanted_id']) ? $item['wanted_id'] = $tor['wanted_id'] : null;

            if ($item['media_type'] == 'movies') {
                MovieJob($item, true);
            } else if ($item['media_type'] == 'shows') {
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
        $log->debug(" No Torrents (INAPP set)");
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
        if ($cfg['move_only_inapp']) {
            foreach ($finished_list as $finished) {
                foreach ($wanted_db as $wanted_item) {
                    if ($wanted_item['hashString'] == $finished['hashString']) {
                        $tors['finished'][] = $finished;
                    }
                }
            }
        } else {
            $tors['finished'] = $finished_list;
        }
    }

    //SEEDING TORS
    if (count($seeding_list) >= 1) {
        if ($cfg['move_only_inapp']) {
            foreach ($seeding_list as $seeding) {
                foreach ($wanted_db as $wanted_item) {
                    if ($wanted_item['hashString'] == $seeding['hashString']) {
                        $tors['seeding'][] = $seeding;
                    }
                }
            }
        } else {
            $tors['seeding'] = $seeding_list;
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
            if (is_array($cfg['MOVIES_PATH'])) {
                $dest_path = $cfg['MOVIES_PATH'][0] . '/' . ucwords($item['title']);
            } else {
                $dest_path = $cfg['MOVIES_PATH'] . '/' . ucwords($item['title']);
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

        foreach ($valid_files as $valid_file) {
            $file_tags = getFileTags($valid_file);
            $ext = substr($valid_file, -4);

            $new_file_name = ucwords($item['title']) . ' ' . $file_tags . $ext;
            $final_dest_path = $dest_path . '/' . $new_file_name;

            if (file_exists($final_dest_path) && !$linked && !is_link($final_dest_path)) {
                $new_file_name = ucwords($item['title']) . ' ' . $file_tags . '[' . $i . ']' . $ext;
                $final_dest_path = $dest_path . '/' . $new_file_name;
                $i++;
            } else if (file_exists($final_dest_path) && $linked) {
                continue;
            }

            if (!$linked) {
                $log->debug(" Moved work: " . $item['hashString']);
                if (move_media($valid_file, $final_dest_path) && ($valid_file == end($valid_files) )) {
                    $log->debug(" Cleaning torrent id/hash:  {$item['tid']} : {$item['hashString']}");
                    $hashes[] = $item['hashString'];
                    file_exists(dirname($valid_file) . '/trackerm-unrar') ? unlink(dirname($valid_file) . '/trackerm-unrar') : null;
                    file_exists(dirname($valid_file) . '.unrar') ? unlink(dirname($valid_file) . '.unrar') : null;

                    $wanted_item = $db->getItemByField('wanted', 'hashString', $item['hashString']);
                    if (!empty($wanted_item)) {
                        if (!empty($cfg['autoclean_moved_wanted'])) {
                            $log->debug("Removing wanted id by move: " . $wanted_item['id']);
                            $db->deleteItemById('wanted', $wanted_item['id']);
                        } else {
                            $log->debug(" Setting to moved wanted id: " . $wanted_item['id']);
                            $update_ary['wanted_status'] = 9;
                            $update_ary['id'] = $wanted_item['id'];
                            $db->updateItemByField('wanted', $update_ary, 'id');
                        }
                    }

                    $trans->deleteHashes($hashes);
                    $work_path = dirname($valid_file);
                    file_exists($work_path) && ($work_path != $cfg['TORRENT_FINISH_PATH']) && (end($valid_files) == $valid_file) ? rmdir($work_path) : null;
                }
            } else {
                $log->debug(" Link Seeding: {$item['tid']} : {$item['hashString']}");
                linking_media($valid_file, $final_dest_path);
                $new_media .= basename($final_dest_path) . "\n";
            }
        }
        !empty($new_media) ? notify_mail(['subject' => $LNG['L_NEW_MEDIA_AVAILABLE'], 'msg' => $new_media]) : null;
    } else {
        $log->info(" No valid files found on torrent with transmission id: {$item['tid']} : {$item['hashString']} ");
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

            // TAG EPISODE NAME STYLE SxxExx
            $SE = getFileEpisode(basename($valid_file));
            if (!empty($SE['season'] && !empty($SE['episode']))) {
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
            //get again title from indiviual files instead of directory
            $title = getFileTitle(basename($valid_file));
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
                $log->debug(" Moved work: {$item['tid']} : {$item['hashString']}");
                if (move_media($valid_file, $final_dest_path) && ($valid_file == end($valid_files) )) {
                    $log->debug(" Cleaning torrent: {$item['tid']} : {$item['hashString']}");
                    $hashes[] = $item['hashString'];
                    file_exists(dirname($valid_file) . '/trackerm-unrar') ? unlink(dirname($valid_file) . '/trackerm-unrar') : null;
                    file_exists(dirname($valid_file) . '.rar.unrar') ? unlink(dirname($valid_file) . '.rar.unrar') : null;

                    $wanted_item = $db->getItemByField('wanted', 'hashString', $item['hashString']);
                    if (!empty($wanted_item)) {
                        if (!empty($cfg['autoclean_moved_wanted'])) {
                            $log->debug("Removing wanted id by move: " . $wanted_item['id']);
                            $db->deleteItemById('wanted', $wanted_item['id']);
                        } else {

                            $log->debug(" Setting to moved wanted {$item['tid']} : {$item['hashString']}");
                            $update_ary['wanted_status'] = 9;
                            $update_ary['id'] = $wanted_item['id'];
                            $db->updateItemByField('wanted', $update_ary, 'id');
                        }
                    }
                    $trans->deleteHashes($hashes);
                    $work_path = dirname($valid_file);
                    file_exists($work_path) && ($work_path != $cfg['TORRENT_FINISH_PATH']) && (end($valid_files) == $valid_file) ? rmdir($work_path) : null;
                }
            } else {
                $log->debug(" Link Seeding: {$item['tid']} : {$item['hashString']}");
                linking_media($valid_file, $final_dest_path);
                $new_media .= basename($final_dest_path) . "\n";
            }
        }
        !empty($new_media) ? notify_mail(['subject' => $LNG['L_NEW_MEDIA_AVAILABLE'], 'msg' => $new_media]) : null;
    } else {
        $log->info("No valid files found on torrent with {$item['tid']} : {$item['hashString']}");
    }
}

function get_valid_files($item) {
    global $cfg, $LNG, $log;

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
                        $log->info("Need unrar $file");
                        exec($unrar);
                        break;
                    } else {
                        $log->info("Unrar flag is set skipping");
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
                    $log->debug("Work path is empty");
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

    if (rename($valid_file, $final_dest_path)) {
        (!empty($cfg['files_usergroup'])) ? chgrp($final_dest_path, $cfg['files_usergroup']) : null;
        umask(0);
        (!empty($cfg['files_perms'])) ? chmod($final_dest_path, octdec("0" . $cfg['files_perms'])) : null;
        $log->info(" Rename sucessful: $valid_file : $final_dest_path");
        $log->addStateMsg('[' . $LNG['L_MOVED'] . '] ' . basename($final_dest_path));
        return true;
    }

    $log->err(" Move failed: $valid_file : $final_dest_path");
    return false;
}

function linking_media($valid_file, $final_dest_path) {
    global $cfg, $log, $LNG;

    if (symlink($valid_file, $final_dest_path)) {
        (!empty($cfg['files_usergroup'])) ? chgrp($valid_file, $cfg['files_usergroup']) : null;
        umask(0);
        (!empty($cfg['files_perms'])) ? chmod($valid_file, octdec("0" . $cfg['files_perms'])) : null;
        $log->info(" Linking sucessful: $valid_file : $final_dest_path");
        $log->addStateMsg('[' . $LNG['L_LINKED'] . '] ' . basename($final_dest_path));
        return true;
    }

    $log->err(" Linking failed: $valid_file : $final_dest_path");
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
        $log->debug("Wanted list empty");
        return false;
    }
    $day_of_week = date("w");

    foreach ($wanted_list as $wanted) {
        $valid_results = [];

        if ($wanted['direct'] == 1) {
            $log->debug("Jumping wanted {$wanted['id']} by direct ");
            continue;
        }

        if (!empty($wanted['track_show'])) {
            $log->debug("Jumping wanted {$wanted['title']} by track_show");
            //TODO send wanted_list to avoid query again
            tracker_shows($wanted);
            continue;
        }

        if (isset($wanted['wanted_status']) && $wanted['wanted_status'] >= 0) {
            $log->debug("Jumping wanted {$wanted['title']} check by state " . $trans->getStatusName($wanted['wanted_status']));
            continue;
        }

        if ($wanted['day_check'] == -1) {
            $log->debug("Jumping wanted {$wanted['title']} check by date, {$LNG['L_NEVER']}");
            continue;
        }

        if (($wanted['day_check'] > 0) && $wanted['day_check'] != $day_of_week) {
            $log->debug("Jumping wanted {$wanted['title']} check by date, today is not {$LNG[$cfg['CHECK_DAYS'][$wanted['day_check']]]}");
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
        $title = $wanted['title'];
        $media_type = $wanted['media_type'];

        if ($media_type == 'movies') {
            $log->debug(' Search for : ' . $title . "[ $media_type ]");

            $search['words'] = $title;
            $results = search_media_torrents($media_type, $search, null, true);
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
            $results = search_media_torrents($media_type, $search, null, true);
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

        $log->debug("********************************************************************************************************");
    }
}

function wanted_check_title($title, $results) {
    global $log;
    $valid = [];

    $words = trim($title);
    foreach ($results as $item) {
        $title = trim(getFileTitle($item['title']));

        if (strcmp(strtolower($title), strtolower($words) == 0)) {
            $log->debug('Wanted: Valid title found ' . $item['title']);
            $valid[] = $item;
        } else {
            $log->debug('Wanted: Title discarded since is not exact *' . strtolower($title) . '*:*' . strtolower($title) . '*');
        }
    }

    return (count($valid) > 0) ? $valid : false;
}

function wanted_check_flags($wanted, $results) {
    global $cfg, $log;
    $noignore = [];
    $torrent_ignore_prefs = [];
    $custom_words_ignore = [];
    $custom_words_require = [];
    $valid_results = [];

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
        foreach ($results as $result) {
            $ignore_flag = 0;

            foreach ($cfg['torrent_ignore_prefs'] as $ignore) {
                if (stripos($result['title'], trim($ignore))) {
                    $ignore_flag = 1;
                    $log->debug('Wanted: Ignored coincidence for item ' . $result['title'] . ' by ignore key ' . $ignore);
                }
            }
            if ($ignore_flag != 1) {
                $noignore[] = $result;
            }
        }
    } else {
        $noignore = $results;
    }

    if (count($cfg['torrent_quality_prefs']) > 0) {

        //build quality array with PROPER and without PROPER
        $_order = 0;
        foreach ($cfg['torrent_quality_prefs'] as $quality) {
            if ($quality == 'ANY') {
                $TORRENT_QUALITYS_PREFS_PROPER[$_order] = 'PROPER';
                if (empty($wanted['only_proper'])) {
                    $TORRENT_QUALITYS_PREFS_PROPER[$_order + 1] = $quality;
                    $_order = $_order + 2;
                } else {
                    $_order = $_order + 1;
                }
            } else {
                $TORRENT_QUALITYS_PREFS_PROPER[$_order] = $quality . ' PROPER';
                if (empty($wanted['only_proper'])) {
                    $TORRENT_QUALITYS_PREFS_PROPER[$_order + 1] = $quality;
                    $_order = $_order + 2;
                } else {
                    $_order = $_order + 1;
                }
            }
        }

        foreach ($TORRENT_QUALITYS_PREFS_PROPER as $quality) {
            foreach ($noignore as $noignore_result) {
                if (stripos($noignore_result['title'], $quality) || $quality == 'ANY') {
                    $log->debug('Wanted: Quality coincidence for item ' . $noignore_result['title'] . ' by quality key ' . $quality);
                    $valid_results[] = $noignore_result;
                }
            }
        }
    } else {
        $valid_results = $noignore;
    }

    if (!empty($cfg['torrent_require_or_prefs']) && (count($cfg['torrent_require_or_prefs']) > 0) && !empty($valid_results) && (count($valid_results) > 0)) {

        foreach ($valid_results as $valid_key => $valid_result) {
            $match = 0;
            $words_checked = '';
            foreach ($cfg['torrent_require_or_prefs'] as $or_prefs) {
                if (strpos($valid_result['title'], $or_prefs)) {
                    $match = 1;
                    break;
                } else {
                    empty($words_checked) ? $words_checked = $or_prefs : $words_checked .= ',' . $or_prefs;
                }
            }
            if ($match === 0) {
                $log->debug('Wanted: Drop valid item by global OR ' . $valid_result['title'] . ' any required words ' . $words_checked);
                unset($valid_results[$valid_key]);
            }
        }
    }

    if (!empty($wanted['custom_words_require']) && !empty($valid_results) && (count($valid_results) > 0)) {
        $custom_words_require = explode(',', $wanted['custom_words_require']);
        foreach ($valid_results as $valid_key => $valid_result) {
            foreach ($custom_words_require as $word_require) {
                if (!(stripos($valid_result['title'], trim($word_require)))) {
                    $log->debug('Wanted: Drop valid item by custom require words ' . $valid_result['title'] . ' required word ' . $word_require);
                    unset($valid_results[$valid_key]);
                }
            }
        }
    }

    if (!empty($wanted['custom_words_ignore']) && count($valid_results) > 0) {
        $custom_words_ignore = explode(',', $wanted['custom_words_ignore']);
        foreach ($valid_results as $valid_key => $valid_result) {
            foreach ($custom_words_ignore as $word_ignore) {
                if ((stripos($valid_result['title'], $word_ignore))) {
                    $log->debug('Wanted: Drop valid item by custom ignore words ' . $valid_result['title'] . ' ignore word ' . trim($word_ignore));
                    unset($valid_results[$valid_key]);
                }
            }
        }
    }

    return count($valid_results) > 0 ? $valid_results : false;
}

function tracker_shows($wanted) {
    global $db, $log;

    $from_season = $wanted['season'];
    $from_episode = $wanted['episode'];
    $tmdb_id = $wanted['themoviedb_id'];

    if (empty($tmdb_id) || empty($from_season) || empty($from_episode)) {
        return false;
    }

    $seasons = [];
    $stmt = $db->query('SELECT season,episode FROM shows_details WHERE themoviedb_id=' . $tmdb_id . '');
    $items = $db->fetchAll($stmt);

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
    $stmt = $db->query("SELECT * FROM wanted WHERE themoviedb_id = $tmdb_id AND media_type = 'shows' AND wanted_status < 5 AND track_show = 0");
    $items_match = $db->fetchAll($stmt);
    $items_match_count = count($items_match);
    //From all the episode that meet the criteria check if already have that item
    //or if already in wanted.
    foreach ($list_episodes as $season => $episodes) {
        foreach ($episodes as $key_episode => $episode) {
            //FIXME: When one episode on track show finish download, wanted state is > 5 but cli not linked the show yet (before track_show is called)
            // neither update library shows. On check "that show not exists" then not drop and try send this chapter again.
            // Next cli run the rescan process already is done and is ok.
            // Fixing calling rebuild show here seems over kill.
            // Get all wanted regarless or wanted_status for drop gump up the max_wanted_track, since > 5 must not count for send next show
            // How is running now: The dup show isn't added because dup but neither the next is and correct show on same cli run, must wait next cli
            // run when shows library is building and detect as a have_show.
            if (have_show($tmdb_id, $season, $episode)) {
                unset($list_episodes[$season][$key_episode]);
            }
            if ($items_match_count > 0) {
                foreach ($items_match as $item_match) {
                    if (($item_match['season'] == $season) && ($item_match['episode'] == $episode)) {
                        unset($list_episodes[$season][$key_episode]);
                        break;
                    }
                }
            }
        }
    }

    $item = mediadb_getFromCache('shows', $tmdb_id);
    $title = $item['title'];
    $max_wanted_track_downloads = 1; //TODO: TO CONFIG

    $inherint_track = null;
    !empty($wanted['custom_words_ignore']) ? $inherint_track['custom_words_ignore'] = $wanted['custom_words_ignore'] : null;
    !empty($wanted['custom_words_require']) ? $inherint_track['custom_words_require'] = $wanted['custom_words_require'] : null;
    !empty($wanted['day_check']) ? $inherint_track['day_check'] = $wanted['day_check'] : null;
    foreach ($list_episodes as $season => $episodes) {
        foreach ($episodes as $key_episode => $episode) {
            if ($items_match_count < $max_wanted_track_downloads) {
                $log->debug("Sending to wanted tracker show episode: $title $season:$episode");
                wanted_episode($tmdb_id, $season, $episode, 0, $inherint_track);
                $items_match_count++;
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

/*
  1º check movies/shows with empty trailer and update if we already not update in DB_UPD_MISSING_DELAY
  2º check no empty trailers movie/shows and update trailer link after DB_UPD_LONG_DELAY
 */

function update_trailers() {
    global $db, $cfg, $log;


    $log->debug('Executing update trailers');
    $limit = 15;

    $tables = ['library_movies', 'library_shows', 'tmdb_search_movies', 'tmdb_search_shows'];

    //IMPROVE: This can be done in one query per table
    // Update missing trailers and never try get trailer
    foreach ($tables as $table) {
        $update = [];

        if ($table == 'library_movies' || $table == 'tmdb_search_movies') {
            $media_type = 'movies';
        } else {
            $media_type = 'shows';
        }

        $update['updated'] = $time_now = time();
        $next_update = time() - $cfg['db_upd_missing_delay'];

        $query = "SELECT DISTINCT themoviedb_id FROM $table WHERE trailer = '' OR  (trailer = '0' AND updated < $next_update) LIMIT $limit";
        $stmt = $db->query($query);

        $results = $db->fetchAll($stmt);

        foreach ($results as $item) {
            $trailer = mediadb_getTrailer($media_type, $item['themoviedb_id']);
            if (!empty($trailer)) {
                if (substr(trim($trailer), 0, 5) != 'https') {
                    $trailer = str_replace('http', 'https', $trailer);
                }
                $update['trailer'] = $trailer;
                $log->debug("Update $table trailer on tmdb_id {$item['themoviedb_id']} trailer $trailer");
            } else {
                $update['trailer'] = 0;
            }
            $where_upd = ['themoviedb_id' => ['value' => $item['themoviedb_id']]];

            $db->update($table, $update, $where_upd);
        }

        // LONG DELAY UPDATE TRAILER
        $update = [];
        $update['updated'] = $time_now = time();
        $next_update = time() - $cfg['db_upd_long_delay'];
        $query = "SELECT themoviedb_id FROM $table WHERE trailer IS NOT NULL AND updated < $next_update LIMIT $limit";
        $stmt = $db->query($query);
        $results = $db->fetchAll($stmt);

        foreach ($results as $item) {
            $trailer = mediadb_getTrailer($media_type, $item['themoviedb_id']);
            if (!empty($trailer)) {
                if (substr(trim($trailer), 0, 5) != 'https') {
                    $trailer = str_replace('http', 'https', $trailer);
                }
                $update['trailer'] = $trailer;
                $log->debug("Update $table trailer on tmdb_id {$item['themoviedb_id']} trailer $trailer");
            } else {
                $update['trailer'] = 0;
            }
            $where_upd = ['themoviedb_id' => ['value' => $item['themoviedb_id']]];

            $db->update($table, $update, $where_upd);
        }
    }
}

function hash_missing() {
    global $db, $log;

    $hashlog = "Hashing: ";
    $query = $db->query('SELECT id,path FROM library_movies WHERE file_hash IS \'\' LIMIT 50');
    $results = $db->fetchAll($query);

    foreach ($results as $item) {
        $hash = file_hash($item['path']);
        $update_query = "update library_movies SET file_hash='$hash' WHERE id='{$item['id']}' LIMIT 1";
        $hashlog .= "*";
        $db->query($update_query);
    }

    $query = $db->query('SELECT id,path FROM library_shows WHERE file_hash IS \'\' LIMIT 50');
    $results = $db->fetchAll($query);

    foreach ($results as $item) {
        $hash = file_hash($item['path']);
        $update_query = "update library_shows SET file_hash='$hash' WHERE id='{$item['id']}' LIMIT 1";
        $hashlog .= "+";
        $db->query($update_query);
    }
    $log->debug($hashlog);
}

function check_broken_files_linked() {
    global $cfg;
    $paths = [];

    if (is_array($cfg['MOVIES_PATH'])) {
        $paths = array_merge($paths, $cfg['MOVIES_PATH']);
    } else {
        $paths[] = $cfg['MOVIES_PATH'];
    }

    if (is_array($cfg['SHOWS_PATH'])) {
        $paths = array_merge($paths, $cfg['SHOWS_PATH']);
    } else {
        $paths[] = $cfg['SHOWS_PATH'];
    }
    RemoveBrokenMedialinks($paths, $cfg['media_ext']);
}

function update_seasons($force = false) {
    global $db, $log;

    $log->debug("Executing update_seasons");

    $update['updated'] = $time_now = time();
    $when_time = time() - 259200; //3 days
    $query = "SELECT DISTINCT themoviedb_id FROM shows_details";
    (!$force) ? $query .= " WHERE updated < $when_time" : " LIMIT 20";
    $stmt = $db->query($query);
    $shows = $db->fetchAll($stmt);
    $i = 0;
    if (valid_array($shows)) {
        foreach ($shows as $show) {
            mediadb_getSeasons($show['themoviedb_id']);
            $where['themoviedb_id'] = ['value' => $show['themoviedb_id']];
            $db->update('shows_details', $update, $where);
            $i++;
        }
    }
    $log->debug("Seasons updated: $i");
}

function delete_direct_orphans() {
    global $trans, $db;

    $items = $db->getItemsByField('wanted', 'direct', 1);
    $torrents = $trans->getAll();

    foreach ($items as $item) {
        $found = 0;
        foreach ($torrents as $torrent) {
            if ($item['hashString'] == $torrent['hashString']) {
                $found = 1;
                break;
            }
        }
        if (!$found) {
            $db->deleteItemById('wanted', $item['id']);
        }
    }
}

function update_things() {
    global $cfg;


    check_broken_files_linked();

    rebuild('movies', $cfg['MOVIES_PATH']);
    sleep(1);
    rebuild('shows', $cfg['SHOWS_PATH']);

    update_trailers();
    update_seasons();
    hash_missing();
    update_stats();
    //delete from wanted orphans (a orphans is create if user delete the torrent outside trackerm
    delete_direct_orphans();

//UPGRADE
    set_clean_titles(); // (upgrading v4 change how clean works, must empty the field and redo )
}

function leave($msg = false) {
    global $log;

    $log->debug('Exit Called');
    !empty($msg) ? $log->err($msg) : null;

    exit();
}
