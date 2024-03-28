<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function curl_get(string $url, array $curl_opt = null) {
    global $cfg, $log;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, trim($url));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $cfg['curl_conntimeout']);
    curl_setopt($ch, CURLOPT_TIMEOUT, $cfg['curl_timeout']);
    (!empty($curl_opt['headers'])) ? curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_opt['headers']) : null;
    (!empty($curl_opt['return_headers'])) ? curl_setopt($ch, CURLOPT_HEADER, 1) : null;

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $log->err('Curl error: ' . curl_error($ch));
        $response = false;
    }
    curl_close($ch);

    return $response;
}

function curl_get_jackett(string $url, string $params) {
    global $cfg;

    $curl_opt['headers'] = [
        'Accept-Encoding: gzip, deflate',
    ];
    $url = $url . $params;

    $cfg['remote_querys_jackett']++;
    $response = curl_get($url, $curl_opt);

    //FIXME tornzb:attr lost by simplexml/json_encode this is a temporary/fast fix
    //do better parsing to array the xml not that shit.

    $response = preg_replace('/:attr/', '', $response);
    //clean empty description
    $response = preg_replace('/<description \/>/', '', $response);

    $xml = @simplexml_load_string($response);

    if (empty($xml)) {
        return false;
    }
    $json = json_encode($xml);
    $array = json_decode($json, TRUE);
    return $array;
}

function curl_get_tmdb(string $url) {
    global $cfg;

    !isset($cfg['TMDB_LANG']) ? $cfg['TMDB_LANG'] = $cfg['LANG'] : null;

    $curl_opt['headers'] = [
        'Content-Type: application/json;charset=utf-8',
        'Accept: text/html,application/xhtml+xml,application/xml,application/json;q=0.9,*/*;q=0.8',
        'Accept-Charset: utf-8;q=0.7,*;q=0.3',
        'Accept-Language:' . $cfg['TMDB_LANG'] . ';q=0.6,' . substr($cfg['TMDB_LANG'], 0, 2) . ';q=0.4'
    ];

    $cfg['remote_querys_tmdb']++;
    $response = curl_get($url, $curl_opt['headers']);

    if ($response) {
        $array = json_decode($response, TRUE);
    } else {
        return false;
    }
    if (isset($array['success']) && !$array['success']) {
        return false;
    }

    return $array;
}
