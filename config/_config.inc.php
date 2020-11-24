<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
global $cfg;

// themoviedb.org api */
$cfg['db_api_token'] = '';
$cfg['search_db'] = 'themoviedb';
// Lang es-ES or en-EN
$cfg['LANG'] = 'es-ES';

// Full path where trackerm reside
$cfg['ROOT_PATH'] = '';

// Relative path (Webserver) where trackerm reside
$cfg['REL_PATH'] = '/trackerm';

// Your Movies and Shows  paths
$cfg['MOVIES_PATH'] = '/home/compartido/biblioteca/Peliculas';
$cfg['SHOWS_PATH'] = '/home/compartido/biblioteca/Series';

// Where transmission put download files (you must separete from temporal file directory
$cfg['TORRENT_FINISH_PATH'] = '/home/compartido/Ultimo';

// (1) will move only media download with trackerm,(0) will scan and move all media download with transmission
$cfg['MOVE_ONLY_INAPP'] = 0;

// With this option we order to trackm search for other media in torrent directory that we example delete the
// transmission torrent instead or paused/stopped
// In this case trackrm will move all matches but not delete the directorys related.

$cfg['MOVE_TRANSMISSION_ORPHAN'] = 1; //NOT AVAILABLE YET
// (1) trackrm will scan apart from check transmission server  for media files in the OTHER_MEDIA_DIR directorys array
// In this case trackrm will move all matches but not delete the directorys related.
// NOT AVAILABLE YET
$cfg['SCAN_MEDIA_DIR'] = 1;
$cfg['OTHER_MEDIA_DIR'] = [
];
// (1) Must create a folder for each movie or  (0) drop all in the MOVIES_PATH
$cfg['CREATE_MOVIE_FOLDERS'] = 1;

// (1) Must create a folder for each season or  (0) drop all in the show directority
$cfg['CREATE_SHOWS_SEASON_FOLDER'] = 1;

// Default group is your transmission group, set a group here if you want change the group after move the files to your library.
$cfg['FILES_USERGROUP'] = 'shares';
$cfg['FILES_PERMS'] = 0664;

// Where your Jackett server reside, his API key, and how many result get per indexer
$cfg['jackett_srv'] = 'http://192.168.X.X:9117';
$cfg['jackett_key'] = '';
$cfg['jacket_results'] = 25;

// What Jacket indexer will use, check in "Actions" links the name ex:  http://192.168.X.XX:9117/api/v2.0/indexers/NAME/results/
$cfg['jackett_indexers'] = [
    0 => 'newpct',
//    1 => 'divxtotal',
    2 => 'mejortorrent',
//    3 => 'moviesdvdr',
//     5 => 'rarbg',
];

// User profiles, at least default must exists
$cfg['profiles'] = [
    0 => 'default',
];

// Transmission connection details
$cfg['trans_hostname'] = '192.168.X.X';
$cfg['trans_port'] = '9091';
$cfg['trans_username'] = '';
$cfg['trans_passwd'] = '';

// Must cache images?
$cfg['CACHE_IMAGES'] = 1;
$cfg['CACHE_IMAGES_PATH'] = '/cache/images';

//When trackerm automatic search for torrents (wanted) will look first if exists
//the torrent with this tags (in order) if found stop looking for torrents and download.
// ANY mean will download any torrent if not meet the other requirements and must be in the last place
// unless you want the first title coincidence whatever the quality.
$cfg['TORRENT_QUALITYS_PREFS'] = [
    0 => '720p',
    1 => '1080p',
    100 => 'ANY',
];

//trackerm (wanted) will ignore all torrents with this words in title.
$cfg['TORRENT_IGNORES_PREFS'] = [
    0 => 'LATINO',
    1 => 'SCREENER',
];

// The space time in seconds for search the wanted elements the day we choose
$cfg['WANTED_DAY_DELAY'] = 3600;
// Theme (actually only default)
$cfg['theme'] = 'default';

/* * ********************************** */
/* PROBABLY NOT NEED EDIT BELOW HERE */
/* * ********************************** */

$cfg['tresults_details'] = 1;
$cfg['max_identify_items'] = 5;
$cfg['tresults_rows'] = 2;
$cfg['tresults_columns'] = 8;
$cfg['profile'] = 0;
$cfg['TORRENT_MEDIA_REGEX'] = '/(\.avi|\.mp4|\.mkv)/i';
$cfg['CHARSET'] = 'UTF8';
$cfg['LOCALE'] = str_replace('-', '_', $cfg['LANG'] . '.' . $cfg['CHARSET']);
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

$cfg['media_ext'] = [
    "mkv",
    "MKV",
    "avi",
    "AVI",
    "mp4",
    "MP4",
];

$cfg['categories'] = $cfg['movies_categories'] + $cfg['shows_categories'];
