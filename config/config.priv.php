<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
/* * ********************************** */
/* PROBABLY NOT NEED CHANGE ANYTHING HERE */
/* * ********************************** */
!defined('IN_WEB') ? exit : true;

$cfg['search_db'] = 'themoviedb';
$cfg['version'] = 'A96';
$cfg['odb_movies_link'] = 'https://www.themoviedb.org/movie/';
$cfg['odb_shows_link'] = 'https://www.themoviedb.org/tv/';
$cfg['odb_images_link'] = 'https://image.tmdb.org/t/p/w500';
$cfg['remote_querys_tmdb'] = 0;
$cfg['remote_querys_jackett'] = 0;

$cfg['DB_FILE'] = $cfg['ROOT_PATH'] . '/cache/trackerm.db';
$cfg['img_url'] = $cfg['REL_PATH'] . '/img';
$cfg['movies_categories'] = [
    2000 => 'Movies',
    2010 => 'Movies/Foreign',
    2020 - 2021 => 'Movies/Other',
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

$cfg['TMDB_GENRES'] = [
    12 => 'L_ADVENTURE',
    14 => 'L_FANTASY',
    16 => 'L_ANIMATION',
    18 => 'L_DRAMA',
    27 => 'L_HORROR',
    28 => 'L_ACTION',
    35 => 'L_COMEDY',
    36 => 'L_HISTORY',
    37 => 'L_WESTERN',
    53 => 'L_THRILLER',
    80 => 'L_CRIME',
    99 => 'L_DOCUMENTARY',
    878 => 'L_SCIFY',
    9648 => 'L_MYSTERY',
    10402 => 'L_MUSIC',
    10749 => 'L_ROMANCE',
    10751 => 'L_FAMILY',
    10752 => 'L_WAR',
    10759 => 'L_ACTION_ADV',
    10762 => 'L_KIDS',
    10763 => 'L_NEWS',
    10764 => 'L_REALITY',
    10765 => 'L_SCIFY_FAN',
    10766 => 'L_SOAP',
    10767 => 'L_TALK',
    10768 => 'L_WAR_POL',
    10770 => 'L_TV_MOVIE',
];
$cfg['tmdb_genres_movies'] = [
    12 => 'L_ADVENTURE',
    14 => 'L_FANTASY',
    16 => 'L_ANIMATION',
    18 => 'L_DRAMA',
    27 => 'L_HORROR',
    28 => 'L_ACTION',
    35 => 'L_COMEDY',
    36 => 'L_HISTORY',
    37 => 'L_WESTERN',
    53 => 'L_THRILLER',
    80 => 'L_CRIME',
    99 => 'L_DOCUMENTARY',
    878 => 'L_SCIFY',
    9648 => 'L_MYSTERY',
    10402 => 'L_MUSIC',
    10749 => 'L_ROMANCE',
    10751 => 'L_FAMILY',
    10752 => 'L_WAR',
    10770 => 'L_TV_MOVIE',
];
$cfg['tmdb_genres_shows'] = [
    16 => 'L_ANIMATION',
    18 => 'L_DRAMA',
    35 => 'L_COMEDY',
    37 => 'L_WESTERN',
    80 => 'L_CRIME',
    99 => 'L_DOCUMENTARY',
    9648 => 'L_MYSTERY',
    10751 => 'L_FAMILY',
    10759 => 'L_ACTION_ADV',
    10762 => 'L_KIDS',
    10763 => 'L_NEWS',
    10764 => 'L_REALITY',
    10765 => 'L_SCIFY_FAN',
    10766 => 'L_SOAP',
    10767 => 'L_TALK',
    10768 => 'L_WAR_POL',
];

$cfg['categories'] = $cfg['movies_categories'] + $cfg['shows_categories'];
