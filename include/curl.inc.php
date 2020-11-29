<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function curl_get($url, $curl_opt) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, trim($url));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if (!empty($curl_opt['headers'])) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_opt['headers']);
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

function curl_get_jackett($url, $params) {

    $curl_opt['headers'] = [
        'Accept-Encoding: gzip, deflate',
    ];
    $url = $url . $params;

    $response = curl_get($url, $curl_opt);

    //FIXME tornzb:attr lost by simplexml/json_encode this is a temporary/fast fix
    //do better parsing to array the xml not that shit.

    $response = preg_replace('/:attr/', '', $response);
    //clean empty description
    $response = preg_replace('/<description \/>/', '', $response);

    $xml = @simplexml_load_string($response);

    if (!$xml) {
        return false;
    }
    $json = json_encode($xml);

    $array = json_decode($json, TRUE);

    return $array;
}

function curl_get_tmdb($url) {
    global $cfg;

    $curl_opt['headers'] = [
        'Content-Type: application/json;charset=utf-8',
        'Accept: text/html,application/xhtml+xml,application/xml,application/json;q=0.9,*/*;q=0.8',
        'Accept-Charset: utf-8;q=0.7,*;q=0.3',
        'Accept-Language:' . $cfg['LANG'] . ';q=0.6,' . substr($cfg['LANG'], 0, 2) . ';q=0.4'
    ];

    $response = curl_get($url, $curl_opt['headers']);

    $array = json_decode($response, TRUE);
    //var_dump($array);

    return $array;
}
