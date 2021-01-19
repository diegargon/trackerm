<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego/@/envigo.net)
 */
/* * ******************************************************************************* */
/* THIS FILE DOES NOTHING  THIS IS ONLY A EXAMPLE OF  MINIMAL REQUIRED CONFIGURATION */
/* copy and rename it to /etc/trackerm.conf and edit                              */
/* * ******************************************************************************* */

// themoviedb.org api key: https://www.themoviedb.org/documentation/api
$cfg['db_api_token'] = '';
// Lang es-ES or en-EN
$cfg['LANG'] = 'en-EN';

// Full path where trackerm reside ex: /var/www/html
$cfg['ROOT_PATH'] = '';

// Relative path (Webserver) where trackerm reside // /trackerm for http://mydomain.com/trackerm
$cfg['REL_PATH'] = '/trackerm';

// Your Movies and Shows  paths (the first element (0) is when drop new content)
$cfg['MOVIES_PATH'] = [
    0 => '', //ex: 0 => '/home/mylib/movies',
];

$cfg['SHOWS_PATH'] = [
    0 => '',
];
// Where transmission put download files (you must separete from temporal file directory
$cfg['TORRENT_FINISH_PATH'] = '';

//Jacket Server ex: http://192.168.1.1:9117
$cfg['jackett_srv'] = '';
//Jacket Key: ex: a long line of characters and number check Jackett
$cfg['jackett_key'] = '';

// What Jacket indexer will use, check link in "Actions" the name ex:  http://192.168.X.XX:9117/api/v2.0/indexers/NAME/results/
$cfg['jackett_indexers'] = [
//    1 => 'newpct',
//    2 => 'divxtotal',
//    3 => 'mejortorrent',
//    4 => 'moviesdvdr',
//     5 => 'rarbg',
];

// Transmission connection details (user/pass can be blank if you not setup passwords)
$cfg['trans_hostname'] = ''; //ex: 192.168.1.1
$cfg['trans_port'] = '9091';
$cfg['trans_username'] = '';
$cfg['trans_passwd'] = '';

// User profiles, at least default must exists
$cfg['profiles'] = [
    1 => 'default',
];

