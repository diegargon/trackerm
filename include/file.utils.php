<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
// https://www.php.net/manual/en/function.glob.php#111217
function findFiles($directory, $extensions = array()) {

    function glob_recursive($directory, &$directories = array()) {
        foreach (glob($directory, GLOB_ONLYDIR | GLOB_NOSORT) as $folder) {
            $directories[] = $folder;
            glob_recursive("{$folder}/*", $directories);
        }
    }

    glob_recursive($directory, $directories);
    $files = array();
    foreach ($directories as $directory) {
        foreach ($extensions as $extension) {
            foreach (glob("{$directory}/*.{$extension}") as $file) {
                $files[] = $file;
            }
        }
    }
    return $files;
}

#

function human_filesize($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
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
        return json_decode(file_get_contents($file), true);
    }

    return false;
}

function cacheImg($img_url) {
    global $cfg;

    if (!is_writeable($_SERVER['DOCUMENT_ROOT'] . $cfg['REL_PATH'] . $cfg['CACHE_IMAGES_PATH'])) {
        return false;
    }

    $file_name = basename($img_url);
    $img_path = $_SERVER['DOCUMENT_ROOT'] . $cfg['REL_PATH'] . $cfg['CACHE_IMAGES_PATH'] . '/' . $file_name;

    if (
            file_exists($img_path) ||
            file_put_contents($img_path, file_get_contents($img_url)) !== false
    ) {
        return $cfg['REL_PATH'] . $cfg['CACHE_IMAGES_PATH'] . '/' . $file_name;
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

        $buffer = fread($fd, 2048);
        print $buffer;
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
    return $data;
}
