<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function scanAppMedia() {
    /*
      global $db, $cfg;

      $log->debug(" [out] Cheking media files in " . $cfg['TORRENT_FINISH_PATH'];


     */
}

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
                $log->debug(" Movie seeding detected tid:[{$item['tid']}] begin linking.. " . $item['title']);
                MovieJob($item, true);
            } else if ($item['media_type'] == 'shows') {
                $log->debug(" Show seeeding detected tid:[{$item['tid']}] begin linking... " . $item['title']);
                ShowJob($item, true);
            }
        }
    }
}

function getRightTorrents() {
    global $cfg, $log, $trans, $db;

    $finished_list = [];
    $seeding_list = [];

    $transfers = $trans->getAll();

    $wanted_db = $db->getTableData('wanted');

    if ($cfg['MOVE_ONLY_INAPP'] && empty($wanted_db)) {
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
        if ($cfg['MOVE_ONLY_INAPP']) {
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
        if ($cfg['MOVE_ONLY_INAPP']) {
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
    global $cfg, $log, $trans, $db;

    $valid_files = [];

    $valid_files = get_valid_files($item);

    if ($valid_files && count($valid_files) >= 1) {

        if ($cfg['CREATE_MOVIE_FOLDERS']) {
            $dest_path = $cfg['MOVIES_PATH'] . '/' . ucwords($item['title']);
            if (!file_exists($dest_path)) {
                umask(0);
                if (!mkdir($dest_path, $cfg['DIR_PERMS'], true)) {
                    leave('Failed to create folders... ' . $dest_path);
                }
                (!empty($cfg['FILES_USERGROUP'])) ? chgrp($dest_path, $cfg['FILES_USERGROUP']) : null;
            }
        } else {
            $dest_path = $cfg['MOVIES_PATH'];
        }

        $i = 1;
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
                //$log->debug(" Linking  " . basename($final_dest_path) . " already done... skipping");
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
                        $log->debug(" Setting to moved wanted id: " . $wanted_item['wanted_id']);
                        $update_ary['wanted_status'] = 9;
                        $update_ary['id'] = $wanted_item['id'];
                        $db->updateItemByField('wanted', $update_ary, 'id');
                    }

                    $trans->deleteHashes($hashes);
                    $work_path = dirname($valid_file);
                    file_exists($work_path) && ($work_path != $cfg['TORRENT_FINISH_PATH']) && (end($valid_files) == $valid_file) ? rmdir($work_path) : null;
                }
            } else {
                $log->debug(" Link Seeding: {$item['tid']} : {$item['hashString']}");
                linking_media($valid_file, $final_dest_path);
            }
        }
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
            if ($cfg['CREATE_SHOWS_SEASON_FOLDER'] && !empty($_season)) {
                ($_season != "xx") ? $_season = (int) $_season : null; // 01 to 1 for directory
                $dest_path = $cfg['SHOWS_PATH'] . '/' . ucwords($title . '/' . $LNG['L_SEASON'] . ' ' . $_season);
                $dest_path_father = $cfg['SHOWS_PATH'] . '/' . ucwords($title);
            } else {
                $dest_path = $cfg['SHOWS_PATH'] . '/' . ucwords($title);
            }
            //END CREATE PATHS
            //CREATE FOLDERS
            if (!file_exists($dest_path)) {
                umask(0);
                if (!mkdir($dest_path, $cfg['DIR_PERMS'], true)) {
                    leave('Failed to create folders... ' . $dest_path);
                }
                if (!empty($cfg['FILES_USERGROUP'])) {
                    chgrp($dest_path, $cfg['FILES_USERGROUP']);
                    isset($dest_path_father) ? chgrp($dest_path_father, $cfg['FILES_USERGROUP']) : null;
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
                //$log->debug(" Linking " . basename($final_dest_path) . " already done... skipping");
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
                        $log->debug(" Setting to moved wanted {$item['tid']} : {$item['hashString']}");
                        $update_ary['wanted_status'] = 9;
                        $update_ary['id'] = $wanted_item['id'];
                        $db->updateItemByField('wanted', $update_ary, 'id');
                    }
                    $trans->deleteHashes($hashes);
                    $work_path = dirname($valid_file);
                    file_exists($work_path) && ($work_path != $cfg['TORRENT_FINISH_PATH']) && (end($valid_files) == $valid_file) ? rmdir($work_path) : null;
                }
            } else {
                $log->debug(" Link Seeding: {$item['tid']} : {$item['hashString']}");
                linking_media($valid_file, $final_dest_path);
            }
        }
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

            if ($ext_check == 'rar' || $ext_check == 'RAR') {
                if (file_exists($cfg['UNRAR_PATH'])) {
                    $unrar_check = dirname($file) . '/trackerm-unrar';
                    if (!file_exists($unrar_check)) {
                        if (check_file_encrypt('rar', $file)) {
                            $log->addStateMsg(" {$LNG['L_ERR_FILE_ENCRYPT_MANUAL']} ($file)");
                            // we continue and try since the function need test and TODO.
                        }
                        touch($unrar_check);
                        !empty($cfg['FILES_USERGROUP']) ? chgrp($unrar_check, $cfg['FILES_USERGROUP']) : null;
                        $unrar = $cfg['UNRAR_PATH'] . ' x -p- -y "' . $file . '" "' . dirname($file) . '"';
                        $log->info("Need unrar $file");
                        exec($unrar);
                        break;
                    } else {
                        $log->info("Unrar flag is set skipping");
                        break;
                    }
                } else {
                    $log->addStateMsg($LNG['L_NEED_UNRAR']);
                }
            }
        }

        isset($unrar) ? $files_dir = scandir_r($orig_path) : false;

        $valid_files = [];

        foreach ($files_dir as $file) {
            if (preg_match($cfg['TORRENT_MEDIA_REGEX'], $file)) {
                $valid_files[] = $file;
            }
        }
    } else {
        $log->debug("$orig_path is not a directory");

        $ext_check = substr($item['files_location'], -3);
        $work_path = $cfg['TORRENT_FINISH_PATH'] . '/' . substr($item['files_location'], 0, -4);
        if ($ext_check == 'rar' || $ext_check == 'RAR') {
            if (!file_exists($work_path)) {
                mkdir($work_path);
            }
            if (file_exists($cfg['UNRAR_PATH'])) {

                $unrar_check = $orig_path . '.unrar';
                if (!file_exists($unrar_check)) {
                    if (check_file_encrypt('rar', $file)) {
                        $log->addStateMsg(" {$LNG['L_ERR_FILE_ENCRYPT_MANUAL']} ($file)");
                        // we continue and try since the function need test and TODO.
                    }
                    $unrar = $cfg['UNRAR_PATH'] . ' e -p- -y "' . $orig_path . '" "' . $work_path . '"';
                    exec($unrar);
                    touch($unrar_check);
                }

                $files_dir = scandir_r($work_path);

                if (empty($files_dir)) {
                    $log->debug("Work path is empty");
                }
                foreach ($files_dir as $file) {
                    if (preg_match($cfg['TORRENT_MEDIA_REGEX'], $file)) {
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
                if (preg_match($cfg['TORRENT_MEDIA_REGEX'], $file['name'])) {
                    $file_full_path = $cfg['TORRENT_FINISH_PATH'] . '/' . $file['name'];
                    $valid_files[] = $file_full_path;
                }
            }
        }
    }

    return $valid_files;
}

function move_media($valid_file, $final_dest_path) {
    global $cfg, $log, $LNG;

    if (rename($valid_file, $final_dest_path)) {
        (!empty($cfg['FILES_USERGROUP'])) ? chgrp($final_dest_path, $cfg['FILES_USERGROUP']) : null;
        (!empty($cfg['FILES_PERMS'])) ? chmod($final_dest_path, $cfg['FILES_PERMS']) : null;
        $log->info(" Rename sucessful: $valid_file : $final_dest_path");
        $log->addStateMsg(basename($final_dest_path) . ' ' . $LNG['L_MOVED_TO_LIBRARY']);
        return true;
    }

    $log->err(" Move failed: $valid_file : $final_dest_path");
    return false;
}

function linking_media($valid_file, $final_dest_path) {
    global $cfg, $log, $LNG;

    if (symlink($valid_file, $final_dest_path)) {
        (!empty($cfg['FILES_USERGROUP'])) ? chgrp($valid_file, $cfg['FILES_USERGROUP']) : null;
        (!empty($cfg['FILES_PERMS'])) ? chmod($valid_file, $cfg['FILES_PERMS']) : null;
        $log->info(" Linking sucessful: $valid_file : $final_dest_path");
        $log->addStateMsg(basename($final_dest_path) . ' ' . $LNG['L_LINKED_TO_LIBRARY']);
        return true;
    }

    $log->err(" Linking failed: $valid_file : $final_dest_path");
    return false;
}

function wanted_work() {
    global $db, $cfg, $LNG, $log, $trans;

    $day_of_week = date("w");

    $wanted_list = $db->getTableData('wanted');
    if (empty($wanted_list) || $wanted_list < 1) {
        $log->debug(" Wanted list empty");
        return false;
    }

    foreach ($wanted_list as $wanted) {
        $valid_results = [];

        if ($wanted['direct'] == 1) {
            $log->debug(" Jumping wanted {$wanted['id']} by direct ");
            continue;
        }

        if (isset($wanted['wanted_status']) && $wanted['wanted_status'] > 0) {
            $log->debug(" Jumping wanted {$wanted['title']} check by state " . $trans->getStatusName($wanted['wanted_status']));
            continue;
        }

        if ($wanted['day_check'] == -1) {
            $log->debug(" Jumping wanted {$wanted['title']} check by date, {$LNG['L_NEVER']}");
            continue;
        }

        if (($wanted['day_check'] > 0) && $wanted['day_check'] == $day_of_week) {
            $log->debug(" Jumping wanted {$wanted['title']} check by date, today is not {$LNG[$cfg['CHECK_DAYS'][$wanted['day_check']]]}");
            continue;
        }

        $last_check = $wanted['last_check'];

        if (!empty($last_check)) {
            $next_check = $last_check + $cfg['WANTED_DAY_DELAY'];
            if ($next_check > time()) {
                $next_check = $next_check - time();
                $log->debug(" Jumping wanted {$wanted['title']} check by delay, next check in $next_check seconds");
                continue;
            }
        }
        $wanted_id = $wanted['id'];
        $themoviedb_id = $wanted['themoviedb_id'];
        $title = $wanted['title'];
        $media_type = $wanted['media_type'];
        $log->debug(" Search for : " . $title . '[' . $media_type . ']');
        if ($media_type == 'movies') {
            $search['words'] = $title;
            $results = search_media_torrents($media_type, $search, null, true);
            if (!empty($results) && count($results) > 0) {
                $valid_results = wanted_check_flags($results);
            } else {
                $log->debug(" No results founds for " . $title);
            }
        } else {
            //$episode = 'S' . $wanted['season'] . 'E' . $wanted['episode'];
            $search['words'] = $title;
            $results = search_media_torrents($media_type, $search, null, true);
            if (!empty($results) && count($results) > 0) {
                $valid_results = wanted_check_flags($results);
            } else {
                $log->debug(" No results founds for " . $title);
            }
        }

        if (!empty($valid_results)) {
            $valid_results[0]['themoviedb_id'] = $themoviedb_id;
            $valid_results[0]['media_type'] = $media_type;
            $valid_results[0]['wanted_id'] = $wanted_id;
            if (send_transmission($valid_results)) {
                $log->addStateMsg($LNG['L_WANTED_FOUND'] . ':(' . $title . ') ' . $LNG['L_DOWNLOADING']);
            }
        } else {
            $update_ary['last_check'] = time();
            $update_ary['first_check'] = 1;
            $db->updateItemById('wanted', $wanted['id'], $update_ary);
        }

        $log->debug("********************************************************************************************************");
    }
}

function wanted_check_flags($results) {
    global $cfg, $log;
    $noignore = [];

    if (count($cfg['TORRENT_IGNORES_PREFS']) > 0) {
        foreach ($results as $result) {
            $ignore_flag = 0;

            foreach ($cfg['TORRENT_IGNORES_PREFS'] as $ignore) {
                if (stripos($result['title'], $ignore)) {
                    $ignore_flag = 1;
                    $log->debug(" Wanted: Ignored coincidence for item " . $result['title'] . " by ignore key " . $ignore);
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

        $_order = 0;
        foreach ($cfg['TORRENT_QUALITYS_PREFS'] as $quality) {
            if ($quality == 'ANY') {
                $TORRENT_QUALITYS_PREFS_PROPER[$_order] = 'PROPER';
                $TORRENT_QUALITYS_PREFS_PROPER[$_order + 1] = $quality;
                $_order = $_order + 2;
            } else {
                $TORRENT_QUALITYS_PREFS_PROPER[$_order] = $quality . ' PROPER';
                $TORRENT_QUALITYS_PREFS_PROPER[$_order + 1] = $quality;
                $_order = $_order + 2;
            }
        }

        foreach ($TORRENT_QUALITYS_PREFS_PROPER as $quality) {
            $desire_quality = 0;

            foreach ($noignore as $noignore_result) {

                if (stripos($noignore_result['title'], $quality) || $quality == 'ANY') {
                    $log->debug(" Wanted: Quality coincidence for item " . $noignore_result['title'] . " by quality key " . $quality);
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
    global $db, $cfg, $trans;

    //var_dump($results);
    foreach ($results as $result) {

        $trans_db = [];

        $d_link = $result['download'];

        ($cfg['WANTED_PAUSED']) ? $trans_opt['paused'] = true : $trans_opt = [];

        $trans_response = $trans->addUrl($d_link, null, $trans_opt);
        foreach ($trans_response as $rkey => $rval) {
            $trans_db[0][$rkey] = $rval;
        }
        //UPDATE WANTED.DB
        if ($cfg['WANTED_PAUSED']) {
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
  First check empty movies/shows with empty trailer a try update if not update in DB_UPD_MISSING_DELAY
  Second check no empty trailers movie/shows and update trailer link width DB_UPD_LONG_DELAY
 */

function update_trailers() {
    global $db, $cfg, $log;

    //TODO: need a code rewrite

    $log->debug('Entering on update trailers');
    $limit = 20;


    //MOVIES_LIBRARY EMPTY TRAILER
    $update = [];

    $update['updated'] = $time_now = time();
    $next_update = time() - $cfg['DB_UPD_MISSING_DELAY'];
    $query = "SELECT DISTINCT themoviedb_id FROM library_movies WHERE trailer IS NULL AND updated < $next_update LIMIT $limit";
    $stmt = $db->query($query);
    $results = $db->fetchAll($stmt);

    foreach ($results as $item) {

        $trailer = mediadb_getTrailer('movies', $item['themoviedb_id']);
        if (!empty($trailer)) {
            $log->debug("Update movie trailer on tmdb_id {$item['themoviedb_id']} trailer $trailer");
            $update['trailer'] = $trailer;
        } else {
            $update['trailer'] = NULL;
        }
        $where_upd = ['themoviedb_id' => ['value' => $item['themoviedb_id']]];

        $db->update('library_movies', $update, $where_upd, 'LIMIT 1');
    }

    //SHOWS_LIBRARY EMPTY TRAILER
    $update = [];
    $update['updated'] = $time_now = time();
    $next_update = time() - $cfg['DB_UPD_MISSING_DELAY'];
    $query = "SELECT DISTINCT themoviedb_id FROM library_shows WHERE trailer IS NULL AND updated < $next_update LIMIT $limit";
    $stmt = $db->query($query);
    $results = $db->fetchAll($stmt);

    foreach ($results as $item) {

        $trailer = mediadb_getTrailer('shows', $item['themoviedb_id']);
        if (!empty($trailer)) {
            $log->debug("Update shows trailer on tmdb_id {$item['themoviedb_id']} trailer $trailer");
            $update['trailer'] = $trailer;
        } else {
            $update['trailer'] = NULL;
        }
        $where_upd = ['themoviedb_id' => ['value' => $item['themoviedb_id']]];
        $db->update('library_shows', $update, $where_upd);
    }

    //MOVIES_LIBRARY LONG DELAY UPDATE TRAILER
    $update = [];
    $update['updated'] = $time_now = time();
    $next_update = time() - $cfg['DB_UPD_LONG_DELAY'];
    $query = "SELECT themoviedb_id FROM library_movies WHERE trailer IS NOT NULL AND updated < $next_update LIMIT $limit";
    $stmt = $db->query($query);
    $results = $db->fetchAll($stmt);

    foreach ($results as $item) {
        $trailer = mediadb_getTrailer('movies', $item['themoviedb_id']);
        if (!empty($trailer)) {
            $log->debug("Update movie trailer on tmdb_id {$item['themoviedb_id']} trailer $trailer");
            $update['trailer'] = $trailer;
        } else {
            $update['trailer'] = NULL;
        }
        $where_upd = ['themoviedb_id' => ['value' => $item['themoviedb_id']]];

        $db->update('library_movies', $update, $where_upd, 'LIMIT 1');
    }

    //SHOWS_LIBRARY LONG UPDATE TRAILER
    $update = [];
    $update['updated'] = $time_now = time();
    $next_update = time() - $cfg['DB_UPD_LONG_DELAY'];
    $query = "SELECT DISTINCT themoviedb_id FROM library_shows WHERE trailer IS NOT NULL AND updated < $next_update LIMIT $limit";
    $stmt = $db->query($query);
    $results = $db->fetchAll($stmt);

    foreach ($results as $item) {

        $trailer = mediadb_getTrailer('shows', $item['themoviedb_id']);
        if (!empty($trailer)) {
            $log->debug("Update shows trailer on  tmdb_id {$item['themoviedb_id']} trailer $trailer");
            $update['trailer'] = $trailer;
        } else {
            $update['trailer'] = NULL;
        }
        $where_upd = ['themoviedb_id' => ['value' => $item['themoviedb_id']]];
        $db->update('library_shows', $update, $where_upd);
    }
}

function leave($msg = false) {
    global $log;

    $log->debug('Exit Called');
    !empty($msg) ? $log->err($msg) : null;

    exit();
}
