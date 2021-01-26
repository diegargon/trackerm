<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego/@/envigo.net)
 */
/* * ********************************** */
/* PROBABLY NOT NEED CHANGE ANYTHING HERE */
/* * ********************************** */

$cfg['search_db'] = 'themoviedb';

$cfg['DB_FILE'] = $cfg['ROOT_PATH'] . '/cache/trackerm.db';
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

$cfg['CHECK_DAYS'] = [
    0 => 'L_DAY_ALL',
    1 => 'L_DAY_MON',
    2 => 'L_DAY_TUE',
    3 => 'L_DAY_WED',
    4 => 'L_DAY_THU',
    5 => 'L_DAY_FRI',
    6 => 'L_DAY_SAT',
    7 => 'L_DAY_SUN',
];

$cfg['categories'] = $cfg['movies_categories'] + $cfg['shows_categories'];
