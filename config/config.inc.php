<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego/@/envigo.net)
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

// if you only want MOVIES and not SHOWS or vice versa  change to 0
$cfg['WANT_MOVIES'] = 1;
$cfg['WANT_SHOWS'] = 1;

// Your Movies and Shows  paths
$cfg['MOVIES_PATH'] = '';
$cfg['SHOWS_PATH'] = '';

// Where transmission put download files (you must separete from temporal file directory
$cfg['TORRENT_FINISH_PATH'] = '';

// (1) will move only media download with trackerm,(0) will scan and move all media download with transmission
$cfg['MOVE_ONLY_INAPP'] = 0;

// With this option we order to trackerm search for other media in torrent directory that we example delete the
// transmission torrent instead or paused/stopped
// In this case trackrm will move all matches but not delete the directorys related.

$cfg['MOVE_TRANSMISSION_ORPHAN'] = 1; //NOT AVAILABLE YET
// (1) trackrm will scan apart from check transmission server  for media files in the OTHER_MEDIA_DIR directorys array
// In this case trackrm will move all matches but not delete the directorys related.
// NOT AVAILABLE YET
//$cfg['SCAN_MEDIA_DIR'] = 1;
//$cfg['OTHER_MEDIA_DIR'] = [];
// (1) Must create a folder for each movie or  (0) drop all in the MOVIES_PATH
$cfg['CREATE_MOVIE_FOLDERS'] = 1;

// (1) Must create a folder for each season or  (0) drop all in the show directority
$cfg['CREATE_SHOWS_SEASON_FOLDER'] = 1;

// Default group is your transmission group, set a group here if you want change the group after move the files or create direcotrys to your library.
$cfg['FILES_USERGROUP'] = '';

//Files and directorys permssions
$cfg['FILES_PERMS'] = 0664;
$cfg['DIR_PERMS'] = 0775;
// Where your Jackett server reside, his API key, and how many result get per indexer
$cfg['jackett_srv'] = 'http://192.168.X.X:9117';
$cfg['jackett_key'] = '';
/* MAX jacket i think its 1000  */
$cfg['jackett_results'] = 500;

// To avoid search for every language in titles for tags, give here what languages want tag.
// Leave blank for avoid languages tag in files. This tags are case sensitive for avoid
// match with titles like "Jonnhy English", use with caution.

$cfg['MEDIA_LANGUAGE_TAG'] = [
    1 => 'SPANISH',
    2 => 'ENGLISH',
    3 => 'CASTELLANO',
    4 => 'ESPAÑOL',
];

//If you want trackerm search for any extr tag in titles add the key here
$cfg['EXTRA_TAG'] = [];

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

// Add wanted in pause 1
$cfg['WANTED_PAUSED'] = 0;

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

// The space time in seconds for search the wanted elements the day we choose for search
// default 3600 seconds. Will try search each hour (if cron is configure for less)
$cfg['WANTED_DAY_DELAY'] = 3600;
// Theme (actually only default)
$cfg['theme'] = 'default';

// Level of log error
$cfg['SYSLOG_LEVEL'] = 'LOG_DEBUG';

//Path to unrar utility
$cfg['UNRAR_PATH'] = '/usr/bin/unrar';

//Cache torrent searching (news too)
$cfg['search_cache'] = 1;
$cfg['search_cache_expire'] = 3600; //seconds

/* * ********************************** */
/* PROBABLY NOT NEED CONFIG BELOW HERE */
/* * ********************************** */

$cfg['TORRENT_MEDIA_REGEX'] = '/(\.avi|\.mp4|\.mkv)/i';

$cfg['media_ext'] = [
    "mkv",
    "MKV",
    "avi",
    "AVI",
    "mp4",
    "MP4",
];

//When missing a field from ex:tmdb we try after few days check if added
$cfg['DB_UPD_MISSING_DELAY'] = 864000; //10 days
//After a long delay we check if there are any change in tmdb entry for update our entry
$cfg['DB_UPD_LONG_DELAY'] = $cfg['DB_UPD_MISSING_DELAY'] * 3;
