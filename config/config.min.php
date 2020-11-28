<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
// themoviedb.org api */
$cfg['db_api_token'] = '';
// Lang es-ES or en-EN
$cfg['LANG'] = '';

// Full path where trackerm reside
$cfg['ROOT_PATH'] = '';

// Relative path (Webserver) where trackerm reside
$cfg['REL_PATH'] = '/trackerm';

// Your Movies and Shows  paths
$cfg['MOVIES_PATH'] = '';
$cfg['SHOWS_PATH'] = '';

// Where transmission put download files (you must separete from temporal file directory
$cfg['TORRENT_FINISH_PATH'] = '';

$cfg['jackett_srv'] = '';
$cfg['jackett_key'] = '';
$cfg['jacket_results'] = 25;

// What Jacket indexer will use, check link in "Actions" the name ex:  http://192.168.X.XX:9117/api/v2.0/indexers/NAME/results/
$cfg['jackett_indexers'] = [
//    0 => 'newpct',
//    1 => 'divxtotal',
//    2 => 'mejortorrent',
//    3 => 'moviesdvdr',
//     5 => 'rarbg',
];

// Transmission connection details
$cfg['trans_hostname'] = '';
$cfg['trans_port'] = '9091';
$cfg['trans_username'] = '';
$cfg['trans_passwd'] = '';
