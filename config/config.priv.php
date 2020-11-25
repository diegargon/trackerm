<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
/* * ********************************** */
/* PROBABLY NOT NEED CONFIG ANYTHING HERE */
/* * ********************************** */

$cfg['app_name'] = 'trackerm';
$cfg['tresults_details'] = 1;
$cfg['max_identify_items'] = 5;
$cfg['tresults_rows'] = 2;
$cfg['tresults_columns'] = 8;
$cfg['profile'] = 0;
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

$cfg['shows_categories'] = [
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

$cfg['categories'] = $cfg['movies_categories'] + $cfg['shows_categories'];
$cfg['VERSION'] = 'A72';

