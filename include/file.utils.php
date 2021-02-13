<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
// https://www.php.net/manual/en/function.glob.php#111217
!defined('IN_WEB') ? exit : true;

function findFiles($directory, $extensions = []) {
    $content = getDirContents($directory);
    $files = [];
    foreach ($content as $file) {
        $ext = getFileExt($file);
        if (in_array($ext, $extensions)) {
            $files[] = $file;
        }
    }
    return $files;
}

function getFileExt($file) {
    return strtolower(substr($file, -3));
}

function getDirContents($dir, &$results = []) {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = $dir . DIRECTORY_SEPARATOR . $value;
        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
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

function cacheImg($img_url) {
    global $cfg;

    if (!is_writeable($_SERVER['DOCUMENT_ROOT'] . $cfg['REL_PATH'] . $cfg['cache_images_path'])) {
        return false;
    }

    $file_name = basename($img_url);
    $img_path = $_SERVER['DOCUMENT_ROOT'] . $cfg['REL_PATH'] . $cfg['cache_images_path'] . '/' . $file_name;

    if (
            file_exists($img_path) ||
            file_put_contents($img_path, file_get_contents($img_url)) !== false
    ) {
        return $cfg['REL_PATH'] . $cfg['cache_images_path'] . '/' . $file_name;
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

function getfile_ary($filename) {
    $data = false;
    if (file_exists($filename)) {
        $data = file_get_contents($filename);
    }
    return !empty($data) ? explode("\n", $data) : false;
}

function getfile($filename) {
    $data = false;
    if (file_exists($filename)) {
        $data = file_get_contents($filename);
    }
    return !empty($data) ? $data : false;
}

function check_file_encrypt($type, $file) {
    global $log;

    if ($type == 'rar') {
        $f = fopen($file, 'rb');
        $s = fread($f, 7);
        if (bin2hex($s) == '526172211a0701') {
            $log->debug('RAR5 signature found');
            fseek($f, 7 + 6);
            $s = fread($f, 1);
            if (bin2hex($s) == "04") {
                $log->debug('RAR5 encryption found');
                return true;
            }
        } else if (bin2hex($s) == '526172211a0700') {
            $log->debug('RAR4 signature found');
            //TODO/FIX Same as RAR5 probably not work
            fseek($f, 7 + 6);
            $s = fread($f, 1);
            if (bin2hex($s) == "04") {
                $log->debug('RAR4 encryption found');
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
