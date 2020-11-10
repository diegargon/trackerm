<?php

global $cfg;


$cfg['db_api_token'] = 'X';
$cfg['search_db'] = 'themoviedb';
$cfg['LANG'] = 'es-ES';
$cfg['cache'] = '/cache';
$cfg['REL_PATH'] = '/trackerm';
$cfg['MOVIES_PATH'] = '/home/compartido/biblioteca/Peliculas';
$cfg['SHOWS_PATH'] = '/home/compartido/biblioteca/Series';
$cfg['max_identify_item'] = 10;
$cfg['LANG'] = 'es';
$cfg['jackett_key'] = 'X';
$cfg['jackett_srv'] = 'http://192.168.X.X:9117';
$cfg['jackett_indexers'] = [
    0 => 'newpct',
    1 => 'cinecalidad',
    2 => 'divxtotal',
    3 => 'mejortorrent',
    4 => 'moviesdvdr',
//     5 => 'rarbg',
];
$cfg['jacket_results'] = 20;

$cfg['trans_hostname'] = '192.168.X.X';
$cfg['trans_port'] = '9091';
$cfg['trans_username'] = '';
$cfg['trans_passwd'] = '';
$cfg['tresults_columns'] = 8;
$cfg['tresults_details'] = 1;
$cfg['tpl_dir'] = 'default';

/* NOT NEED EDIT */

$cfg['jackett_api'] = '/api/v2.0';
$cfg['img_url'] = $cfg['REL_PATH'] . '/img';
$cfg['movies_categories'] = [
    2000 => 'Movies',
    2010 => 'Movies/Foreign',
    2020 => 'Movies/Other',
    2030 => 'Movies/SD',
    2040 => 'Movies/HD',
    2045 => 'Movies/UHD',
    2050 => 'Movies/BluRay',
    2060 => 'Movies/3D',
];

$cfg['tv_categories'] = [
    5000 => 'TV',
    5020 => 'TV/Foreign',
    5030 => 'TV/SD',
    5040 => 'TV/HD',
    5045 => 'TV/UHD',
    5050 => 'TV/Other',
    5060 => 'TV/Sport',
    5070 => 'TV/Anime',
    5080 => 'TV/Documentary'
];

$cfg['media_ext'] = [
    "mkv",
    "MKV",
    "avi",
    "AVI",
    "mp4",
    "MP4",
];

$cfg['categories'] = $cfg['movies_categories'] + $cfg['tv_categories'];
