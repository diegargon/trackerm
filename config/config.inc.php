<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

global $cfg;

// themoviedb.org api */
$cfg['db_api_token'] = '';
$cfg['search_db'] = 'themoviedb';
// Lang es-ES or en-EN
$cfg['LANG'] = 'en-EN';

// Full path where trackerm reside
$cfg['ROOT_PATH'] = '';

// Relative path (Webserver) where trackerm reside
$cfg['REL_PATH'] = '/trackerm';

// Your Movies and Shows  paths
$cfg['MOVIES_PATH'] = [
    1 => '',
];
//In case of more path select where we move your new download content (index)
$cfg['MOVIES_PATH_NEW'] = 1;

$cfg['SHOWS_PATH'] = [
    1 => '',
];
$cfg['SHOWS_PATH_NEW'] = 1;

// Where transmission put download files (you must separete from temporal file directory
$cfg['TORRENT_FINISH_PATH'] = '';

// Where your Jackett server reside, his API key, and how many result get per indexer
$cfg['jackett_srv'] = 'http://192.168.X.X:9117';
$cfg['jackett_key'] = '';

// What Jacket indexer will use, check in "Actions" links the name ex:  http://192.168.X.XX:9117/api/v2.0/indexers/NAME/results/
$cfg['jackett_indexers'] = [
//    1 => 'newpct',
//    2 => 'divxtotal',
//    3 => 'mejortorrent',
//    4 => 'moviesdvdr',
//     5 => 'rarbg',
];

// User profiles, at least default must exists
$cfg['profiles'] = [
    1 => 'default',
];

// Transmission connection details
$cfg['trans_hostname'] = '192.168.1.1';
$cfg['trans_port'] = '9091';
$cfg['trans_username'] = '';
$cfg['trans_passwd'] = '';
