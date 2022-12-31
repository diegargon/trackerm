<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function find_media_files($directory, $extensions = []) {
    $content = get_dir_contents($directory);
    $files = [];
    foreach ($content as $file) {
        $ext = get_file_ext($file);
        if (in_array($ext, $extensions)) {
            $files[] = $file;
        }
    }
    return $files;
}

function is_media_file($file) {
    global $cfg;

    $ext = get_file_ext($file);
    if (in_array($ext, $cfg['media_ext'])) {
        return true;
    }
    return false;
}

function remove_broken_medialinks($paths, $extensions = []) {
    $links = [];
    $files = [];

    foreach ($paths as $path) {
        $files = array_merge($files, find_media_files($path, $extensions));
    }

    foreach ($files as $file) {
        if (is_link($file) && !file_exists($file)) {
            unlink($file);
        }
    }
    return $links;
}

function get_file_ext($file) {
    return strtolower(substr($file, -3));
}

function get_dir_contents($dir, &$results = []) {
    global $log;

    if (!is_readable($dir)) {
        $log->warning("Directory $dir is not readable");
        return $results;
    }
    $files = scandir($dir);

    foreach ($files as $value) {
        $path = $dir . DIRECTORY_SEPARATOR . $value;
        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($value != '.' && $value != '..') {
            get_dir_contents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}

/* opendir/closedir version */

function _get_dir_contents($dir, &$results = []) {
    global $log;

    if (!is_readable($dir)) {
        $log->warning("Directory $dir is not readable");
        return $results;
    }

    if (is_dir($dir)) {
        if (($dh = opendir($dir))) {
            while (($file = readdir($dh)) !== false) {
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (!is_dir($path)) {
                    $results[] = $path;
                } else if ($file != '.' && $file != '..') {
                    _get_dir_contents($path, $results);
                    $results[] = $path;
                }
            }
            closedir($dh);
        }
    }
    return $results;
}

function save_to_file_json($file, $path, $data) {
    if (is_writable($path)) {
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return true;
    }
    return false;
}

function load_from_file_json($file) {
    if (file_exists($file)) {
        $file_content = file_get_contents($file);

        return !empty($file_content) ? json_decode($file_content, true) : false;
    }

    return false;
}

function cache_img($img_url) {
    global $cfg, $log;

    if (empty($img_url) || is_dir($img_url)) {
        return false;
    }

    $path = $_SERVER['DOCUMENT_ROOT'] . $cfg['REL_PATH'] . $cfg['cache_images_path'];
    if (!is_writeable($path)) {
        $log->warning($path . ' is not writable');
        return false;
    }

    $file_name = basename($img_url);
    //sometimes jackett not send a direct  link get name from  ?path=354252&file=poster  if exists
    //file name too long.. simple cut...
    $alt_file_name = get_string_between($file_name, 'path=', '&file=poster');
    if (!empty($alt_file_name)) {
        $file_name = cut_string($alt_file_name, 64);
    }
    $img_path = $path . '/' . $file_name;

    if (file_exists($img_path)) {
        return $cfg['REL_PATH'] . $cfg['cache_images_path'] . '/' . $file_name;
    } else {
        $http_options['timeout'] = $cfg['proxy_timeout'];

        if (!empty($cfg['proxy_enable']) && !empty($cfg['proxy_url'])) {
            if (!empty($cfg['proxy_user']) && !empty($cfg['proxy_pass'])) {
                $auth = base64_encode($cfg['proxy_user'] . ':' . $cfg['proxy_pass']);
            }

            $http_options['proxy'] = $cfg['proxy_url'];
            $http_options['request_fulluri'] = true;
            if (!empty($auth)) {
                $http_options['header'] = "Proxy-Authorization: Basic $auth";
            }
        }
        $ctx = stream_context_create(['http' => $http_options]);
        $img_file = @file_get_contents($img_url, false, $ctx);
        if ($img_file !== false) {
            if (file_put_contents($img_path, $img_file) !== false) {
                return $cfg['REL_PATH'] . $cfg['cache_images_path'] . '/' . $file_name;
            }
        }
    }

    return false;
}

function send_file($path) {
    $public_name = basename($path);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $path);

    header("Content-Disposition: attachment; filename=$public_name;");
    header("Content-Type: $mime_type");
    header("Pragma: ");
    header('Content-Length: ' . filesize($path));
    header("Connection: close");

    $fd = fopen($path, 'rb');
    session_write_close();
    ob_flush();
    flush();

    while (!feof($fd)) {
        /*
          $buffer = fread($fd, 2048);
          print $buffer;
         *
         */
        print stream_get_contents($fd, 32 * 1024);
    }

    fclose($fd);
    exit;
}

function scandir_r($dir) {
    $root = scandir($dir);
    foreach ($root as $value) {
        if ($value === '.' || $value === '..') {
            continue;
        }
        if (is_file("$dir/$value")) {
            $result[] = "$dir/$value";
            continue;
        }
        foreach (scandir_r("$dir/$value") as $value) {
            $result[] = $value;
        }
    }
    return isset($result) ? $result : false;
}

function getfile_ary($filename, $max = 100000) {
    $data = '';
    if (file_exists($filename)) {
        $fsize = filesize($filename);
        $max > $fsize ? $max = $fsize : null;
        $data = file_get_contents($filename, false, null, 0, $max);
    }
    return !empty($data) ? explode("\n", $data) : false;
}

function getfile_log($filename, $max = 100000) {
    $data = '';
    if (file_exists($filename)) {
        $fsize = filesize($filename);
        $max > $fsize ? $max = $fsize : null;
        $data = file_get_contents($filename, false, null, -1 * $max);
    }
    return !empty($data) ? explode("\n", $data) : false;
}

function getfile($filename, $max = 100000) {
    $data = '';

    if (file_exists($filename)) {
        $fsize = filesize($filename);
        $max > $fsize ? $max = $fsize : null;
        $data = file_get_contents($filename, false, null, 0, $max);
    }
    return !empty($data) ? $data : false;
}

function check_file_encrypt($type, $file) {
    global $log;

    //TODO: Probably not doing this right. The 04 seems work with some protected rar
    // but not with all
    if ($type == 'rar') {
        $f = fopen($file, 'rb');
        $s = fread($f, 7);
        if (bin2hex($s) == '526172211a0701') {
            $log->info('RAR5 signature found (' . bin2hex($s) . ')');
            fseek($f, 7 + 6);
            $s = fread($f, 1);
            if (bin2hex($s) == "04") {
                $log->debug('RAR5 protected found');
                return true;
            }
        } else if (bin2hex($s) == '526172211a0700') {
            //FIX: Except signature all same as RAR5 probably not work
            $log->info('RAR4 signature found (' . bin2hex($s) . ')');
            fseek($f, 7 + 6);
            $s = fread($f, 1);
            if (bin2hex($s) == "04") {
                $log->debug('RAR4 protected found');
                return true;
            }
        } else {
            $log->warning("Unknown RAR signature: really a rar file?: $s");
        }
        fclose($f);
    } else {
        $log->warning("Check file encryption: filetype $type not supported ");
    }

    return false;
}

function file_hash($file) {

    if (!file_exists($file)) {
        return false;
    }
    $length = 4096;

    $fp = fopen($file, 'rb');
    $first_data = fread($fp, $length);
    fseek($fp, ($length * -1), SEEK_END);
    $last_data = fgets($fp, $length);
    fclose($fp);

    $data = $first_data . $last_data;

    return (!empty($data)) ? hash('md5', $data) : null;
}

function mediainfo_json($file) {
    global $cfg, $log;
    if (!file_exists($cfg['mediainfo_path'])) {
        $log->err("Wrong mediainfo path");
        return false;
    }
    if (!file_exists(($file))) {
        $log->warning("Mediainfo: Media file not exists: $file");
        return false;
    }

    $mediainfo = $cfg['mediainfo_path'];
    $mediainfo_opts = ' --Output=JSON ';
    $file = '"' . $file . '"';
    $mediainfo_exec = $mediainfo . $mediainfo_opts . $file;
    $mediainfo_json = shell_exec($mediainfo_exec);

    return $mediainfo_json;
}

function mediainfo($file) {
    $json = mediainfo_json($file);
    if ($json) {
        $mediainfo_ary = json_decode($json, true);
    } else {
        return false;
    }

    return isset($mediainfo_ary['media']['track']) ? $mediainfo_ary['media']['track'] : false;
}

function mediainfo_formated($file) {
    $mediainfo = mediainfo($file);
    if (!$mediainfo) {
        return false;
    }
    $mediainfo_tags = [];

    foreach ($mediainfo as $media_item) {
        $type = $media_item['@type'];
        if ($type == 'General') {
            foreach ($media_item as $item_key => $item_value) {
                $mediainfo_tags[$type][$item_key] = $item_value;
            }
        } else {
            if (isset($media_item['StreamOrder'])) {
                $order = $media_item['StreamOrder'];
            } else if (isset($media_item['ID'])) {
                $order = $media_item['ID'];
            } else {
                continue;
            }
            foreach ($media_item as $item_key => $item_value) {
                $mediainfo_tags[$type][$order][$item_key] = $item_value;
            }
        }
    }
    return $mediainfo_tags;
}

function move_file($origin, $destination) {
    global $log;
    /*
      Rename across partitions fail. We try (for same partition)
      and if fail the file must be  copy to other partition
     */
    if (file_exists($origin)) {
        if (@rename($origin, $destination)) {
            return true;
        } else {
            if (file_exists($destination) && is_link($destination)) { //link
                unlink($destination);
            } else if (file_exists($destination)) {
                $log->err("Cant move file, another file with same path/name exists: " . $destination);
                return false;
            }

            if (copy($origin, $destination)) {
                unlink($origin);
                return true;
            }
        }
    }

    return false;
}

function fix_perms() {
    global $cfg;

    foreach (['movies', 'shows'] as $media_type) {
        if ($media_type == 'movies') {
            $paths = $cfg['MOVIES_PATH'];
        } else {
            $paths = $cfg['SHOWS_PATH'];
        }



        foreach ($paths as $path) {
            $results = [];
            get_dir_contents($path, $results);
            foreach ($results as $file) {
                chgrp($file, $cfg['files_usergroup']);
            }
        }
    }
}

function rrmdir($dir, $safe_path) {
    global $log;

    //safe: check if dir is winin working path
    if (!v7_str_starts_with($dir, $safe_path)) {
        $log->addStatusMsg('rrmdir start with fail');
        return false;
    }
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

//PHP8 str_start_with
function v7_str_starts_with($haystack, $needle) {
    return (string) $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
}
