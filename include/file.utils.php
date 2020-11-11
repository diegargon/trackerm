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
