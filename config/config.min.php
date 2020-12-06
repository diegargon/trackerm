<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
/* * ******************************************************************************* */
/* THIS FILE DOES NOTHING  THIS IS ONLY A EXAMPLE OF  MINIMAL REQUIRED CONFIGURATION */
/* copy and rename it to /etc/trackerm.conf and edit                              */
/* * ******************************************************************************* */

// themoviedb.org api */
$cfg['db_api_token'] = '';
// Lang es-ES or en-EN
$cfg['LANG'] = 'en-EN';

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

// What Jacket indexer will use, check link in "Actions" the name ex:  http://192.168.X.XX:9117/api/v2.0/indexers/NAME/results/
$cfg['jackett_indexers'] = [
//    1 => 'newpct',
//    2 => 'divxtotal',
//    3 => 'mejortorrent',
//    4 => 'moviesdvdr',
//     5 => 'rarbg',
];

// Transmission connection details (user/pass can be blank if you not setup passwords)
$cfg['trans_hostname'] = '';
$cfg['trans_port'] = '9091';
$cfg['trans_username'] = '';
$cfg['trans_passwd'] = '';
