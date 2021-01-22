<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego/@/envigo.net)
 */
function create_db() {
    global $db;

    //DB_INFO
    $db->query('CREATE TABLE IF NOT EXISTS "db_info" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "version" INTEGER NOT NULL,
                    "app_name" VARCHAR NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
                )');

    $db->insert('db_info', ["app_name" => 'trackerm', "version" => 5]);

    // USERS
    //"sid" varchar NULL,
    //"isAdmin" INT NOT NULL,  (insert default with isAdmin=1
    //profile image
    $db->query('CREATE TABLE IF NOT EXISTS "users" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "username" varchar NOT NULL UNIQUE,
                    "password" varchar NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
       )');

    $db->insert('users', ["username" => "default"]);

    // PREFERENCES
    $db->query('CREATE TABLE IF NOT EXISTS "preferences" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "uid" INTEGER NOT NULL,
                    "pref_name" VARCHAR NOT NULL,
                    "pref_value" VARCHAR NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    UNIQUE (uid, pref_name)
                )');

    // TMDB_SEARCH
    $db->query('CREATE TABLE IF NOT EXISTS "tmdb_search" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "themoviedb_id" INTEGER NOT NULL,
                    "ilink" VARCHAR NULL,
                    "title" VARCHAR NOT NULL,
                    "clean_title" VARCHAR NULL,
                    "original_title" VARCHAR NULL,
                    "media_type" VARCHAR NULL,
                    "rating" REAL NULL,
                    "popularity" REAL NULL,
                    "elink" VARCHAR NULL,
                    "poster" VARCHAR NULL,
                    "scene" VARCHAR NULL,
                    "trailer" VARCHAR NULL,
                    "lang" VARCHAR NULL,
                    "plot" VARCHAR NULL,
                    "release" VARCHAR NULL,
                    "in_library" INT NULL,
                    "updated" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    UNIQUE(themoviedb_id, media_type)
                )');

    // LOG MSGS
    $db->query('CREATE TABLE IF NOT EXISTS "log_msgs" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "type" VARCHAR NOT NULL,
                    "msg" VARCHAR NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
                )');


    // LIBRARY MOVIES
    $db->query('CREATE TABLE IF NOT EXISTS "library_movies" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "title" VARCHAR NULL,
                    "clean_title" VARCHAR NULL,
                    "themoviedb_id" INTEGER NULL UNIQUE,
                    "file_name" VARCHAR NOT NULL UNIQUE,
                    "predictible_title" VARCHAR NULL,
                    "original_title" VARCHAR NULL,
                    "ilink" VARCHAR NULL,
                    "size" INTEGER NULL,
                    "path" VARCHAR NULL,
                    "file_hash" VARCHAR NULL,
                    "tags" VARCHAR NULL,
                    "ext" VARCHAR NULL,
                    "rating" REAL NULL,
                    "popularity" REAL NULL,
                    "scene" VARCHAR NULL,
                    "lang" VARCHAR NULL,
                    "plot" VARCHAR NULL,
                    "title_year" VARCHAR NULL,
                    "trailer" VARCHAR NULL,
                    "poster" VARCHAR NULL,
                    "release" VARCHAR NULL,
                    "master" INTEGER NULL,
                    "updated" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "added" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
                )');

    //LIBRARY SHOWS
    $db->query('CREATE TABLE IF NOT EXISTS "library_shows" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "title" VARCHAR NULL,
                    "clean_title" VARCHAR NULL,
                    "themoviedb_id" INTEGER NULL,
                    "file_name" VARCHAR NOT NULL UNIQUE,
                    "predictible_title" VARCHAR NULL,
                    "original_title" VARCHAR NULL,
                    "ilink" VARCHAR NULL,
                    "size" INTEGER NULL,
                    "path" VARCHAR NULL,
                    "file_hash" VARCHAR NULL,
                    "tags" VARCHAR NULL,
                    "ext" VARCHAR NULL,
                    "rating" REAL NULL,
                    "popularity" REAL NULL,
                    "scene" VARCHAR NULL,
                    "lang" VARCHAR NULL,
                    "plot" VARCHAR NULL,
                    "season" INTEGER NULL,
                    "episode" INTEGER NULL,
                    "title_year" VARCHAR NULL,
                    "trailer" VARCHAR NULL,
                    "poster" VARCHAR NULL,
                    "release" VARCHAR NULL,
                    "master" INTEGER NULL,
                    "updated" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "added" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
                )');

    //LIBRARY HISTORY
    $db->query('CREATE TABLE IF NOT EXISTS "library_history" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "title" VARCHAR NULL,
                    "clean_title" VARCHAR NULL,
                    "themoviedb_id" INTEGER NULL,
                    "media_type" VARCHAR NULL,
                    "file_name" VARCHAR NOT NULL,
                    "size" INTEGER NULL,
                    "file_hash" VARCHAR NULL,
                    "season" INTEGER NULL,
                    "episode" INTEGER NULL,
                    "added" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
                )');

    //JACKET MOVIES
    $db->query('CREATE TABLE IF NOT EXISTS "jackett_movies" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "title" VARCHAR NOT NULL,
                    "clean_title" VARCHAR NULL,
                    "guid" VARCHAR NOT NULL UNIQUE,
                    "download" VARCHAR NOT NULL,
                    "ilink" VARCHAR NULL,
                    "lang" VARCHAR NULL,
                    "release" VARCHAR NULL,
                    "size" INTEGER NULL,
                    "plot" VARCHAR NULL,
                    "files" VARCHAR NULL,
                    "category" INTEGER NULL,
                    "source" VARCHAR NULL,
                    "poster" VARCHAR NULL,
                    "guessed_poster" VARCHAR NULL,
                    "trailer" VARCHAR NULL,
                    "guessed_trailer" VARCHAR NULL,
                    "added" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
                )');

    //JACKET SHOWS
    $db->query('CREATE TABLE IF NOT EXISTS "jackett_shows" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "title" VARCHAR NOT NULL,
                    "clean_title" VARCHAR NULL,
                    "guid" VARCHAR NOT NULL UNIQUE,
                    "download" VARCHAR NOT NULL,
                    "ilink" VARCHAR NULL,
                    "lang" VARCHAR NULL,
                    "release" VARCHAR NULL,
                    "size" INTEGER NULL,
                    "plot" VARCHAR NULL,
                    "files" VARCHAR NULL,
                    "category" INTEGER NULL,
                    "source" VARCHAR NULL,
                    "poster" VARCHAR NULL,
                    "guessed_poster" VARCHAR NULL,
                    "trailer" VARCHAR NULL,
                    "guessed_trailer" VARCHAR NULL,
                    "added" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
                )');

    //JACKET SEARCH MOVIES CACHE
    $db->query('CREATE TABLE IF NOT EXISTS "jackett_search_movies_cache" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "words" VARCHAR NULL,
                    "media_type" VARCHAR NULL,
                    "ids" VARCHAR NULL,
                    "updated" INTEGER NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
        )');

    //JACKET SEARCH SHOWS CACHE
    $db->query('CREATE TABLE IF NOT EXISTS "jackett_search_shows_cache" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "words" VARCHAR NULL,
                    "media_type" VARCHAR NULL,
                    "ids" VARCHAR NULL,
                    "updated" INTEGER NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
        )');

    //WANTED
    $db->query('CREATE TABLE IF NOT EXISTS "wanted" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "themoviedb_id" INTEGER NULL,
                    "title" VARCHAR NULL,
                    "season" INTEGER  NULL,
                    "episode" INTEGER NULL,
                    "quality" INTEGER NULL,
                    "ignores" INTEGER NULL,
                    "ignore" INTEGER NULL,
                    "custom_words_ignore" VARCHAR NULL,
                    "custom_words_require" VARCHAR NULL,
                    "exact_title" INTEGER NULL,
                    "hashString" VARCHAR NULL,
                    "tid" INTEGER NULL,
                    "first_check" INTEGER NULL,
                    "day_check" INTEGER NULL,
                    "last_check" INTEGER NULL,
                    "direct" INTEGER NULL,
                    "wanted_status" INTEGER NULL,
                    "track_show" INTEGER NULL,
                    "media_type" VARCHAR NULL,
                    "profile" INTEGER NULL,
                    "added" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
        )');
    //SHOWS DETAILS
    $db->query('CREATE TABLE IF NOT EXISTS "shows_details" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "themoviedb_id" INTEGER NOT NULL,
                    "seasons" INTEGER NOT  NULL,
                    "episodes" INTEGER NOT NULL,
                    "release" VARCHAR NULL,
                    "season" INTEGER NOT NULL,
                    "episode" INTEGER NOT NULL,
                    "episode_release" INTEGER NULL,
                    "title" VARCHAR NULL,
                    "clean_title" VARCHAR NULL,
                    "plot" VARCHAR NULL,
                    "updated" INTEGER NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    UNIQUE (themoviedb_id, season, episode)
                )');

    // CONFIG
    // type: 1 string, 2 int, 3 bool, 4 reserve, 5 reserve 6 reserve  7 mixedarray, 8 stringarray, 9 intarray,
    $db->query('CREATE TABLE IF NOT EXISTS "config" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "cfg_key" VARCHAR NOT NULL,
                    "cfg_value" VARCHAR NOT NULL,
                    "cfg_perms" VARCHAR NULL,
                    "cfg_desc" VARCHAR NULL,
                    "type" VARCHAR NOT NULL,
                    "category" VARCHAR NOT NULL,
                    "public" INT NULL,
                    "modify" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    UNIQUE (cfg_key)
                )');

    $db->insert('config', ['cfg_key' => 'version', 'cfg_value' => 'A77', 'cfg_desc' => '', 'type' => 2, 'category' => '', 'public' => 0]);
    $db->insert('config', ['cfg_key' => 'profile', 'cfg_value' => 0, 'cfg_desc' => '', 'type' => 2, 'category' => '', 'public' => 0]);
    $db->insert('config', ['cfg_key' => 'max_identify_items', 'cfg_value' => 5, 'cfg_desc' => 'L_CFG_MAXID_ITEMS', 'type' => 2, 'category' => '', 'public' => 0]);
    $db->insert('config', ['cfg_key' => 'app_name', 'cfg_value' => 'trackerm', 'cfg_desc' => '', 'type' => 1, 'category' => '', 'public' => 0]);
    $db->insert('config', ['cfg_key' => 'tresults_rows', 'cfg_value' => 2, 'cfg_desc' => 'L_CFG_ROWS', 'type' => 2, 'category' => 'L_DISPLAY', 'public' => 0]);
    $db->insert('config', ['cfg_key' => 'new_cache_expire', 'cfg_value' => 3600, 'cfg_desc' => 'L_CFG_NEW_CACHE_EXPIRE', 'type' => 2, 'category' => 'L_MAIN', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'torrent_media_regex', 'cfg_value' => '/(\.avi|\.mp4|\.mkv)/i', 'cfg_desc' => 'L_CFG_TORRENT_MEDIA_REGEX', 'type' => 1, 'category' => 'L_MAIN', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'db_upd_missing_delay', 'cfg_value' => 864000, 'cfg_desc' => 'L_CFG_UPD_MISSING_DELAY', 'type' => 2, 'category' => 'L_MAIN', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'db_upd_long_delay', 'cfg_value' => 2592000, 'cfg_desc' => 'L_CFG_UPD_LONG_DELAY', 'type' => 2, 'category' => 'L_MAIN', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'want_movies', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_WANT_MOVIES', 'type' => 3, 'category' => 'L_MAIN', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'want_shows', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_WANT_SHOWS', 'type' => 3, 'category' => 'L_MAIN', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'wanted_day_delay', 'cfg_value' => 3000, 'cfg_desc' => 'L_CFG_WANT_DAY_DELAY', 'type' => 2, 'category' => 'L_MAIN', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'charset', 'cfg_value' => 'UTF-8', 'cfg_desc' => 'L_CFG_CHARSET', 'type' => 1, 'category' => 'L_LANG', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'locale', 'cfg_value' => 'en_EN.UTF-8', 'cfg_desc' => 'L_CFG_LOCALE', 'type' => 1, 'category' => 'L_LANG', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'theme', 'cfg_value' => 'default', 'cfg_desc' => 'L_CFG_THEME', 'type' => 1, 'category' => 'L_DISPLAY', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'tresults_columns', 'cfg_value' => 8, 'cfg_desc' => 'L_CFG_COLUMNS', 'type' => 2, 'category' => 'L_DISPLAY', 'public' => 0]);
    $db->insert('config', ['cfg_key' => 'jackett_api_path', 'cfg_value' => '/api/v2.0', 'cfg_desc' => 'L_CFG_JACKETT_API_PATH', 'type' => 2, 'category' => 'L_JACKETT', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'jackett_results', 'cfg_value' => 50, 'cfg_desc' => 'L_CFG_JACKETT_RESULTS', 'type' => 2, 'category' => 'L_JACKETT', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'log_to_syslog', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_LOG_TO_SYSLOG', 'type' => 3, 'category' => 'L_LOGGING', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'log_to_file', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_LOG_TO_FILE', 'type' => 3, 'category' => 'L_LOGGING', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'syslog_level', 'cfg_value' => 'LOG_DEBUG', 'cfg_desc' => 'L_CFG_SYSLOG_LEVEL', 'type' => 1, 'category' => 'L_LOGGING', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'search_cache', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_SEARCH_CACHE', 'type' => 3, 'category' => 'L_SEARCH', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'search_cache_expire', 'cfg_value' => 3600, 'cfg_desc' => 'L_CFG_SEARCH_CACHE_EXPIRE', 'type' => 2, 'category' => 'L_SEARCH', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'wanted_paused', 'cfg_value' => 0, 'cfg_desc' => 'L_CFG_WANTED_PAUSED', 'type' => 3, 'category' => 'L_WANTED', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'cache_images', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_CACHE_IMAGES', 'type' => 3, 'category' => 'L_IMAGES', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'cache_images_path', 'cfg_value' => '/cache/images', 'cfg_desc' => 'L_CFG_CACHE_IMAGES_PATH', 'type' => 1, 'category' => 'L_IMAGES', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'unrar_path', 'cfg_value' => '/usr/bin/unrar', 'cfg_desc' => 'L_CFG_UNRAR_PATH', 'type' => 1, 'category' => 'L_FILES', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'create_movie_folders', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_CREATE_MOVIE_FOLDERS', 'type' => 3, 'category' => 'L_FILES', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'create_shows_season_folder', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_CREATE_SHOWS_SEASON_FOLDER', 'type' => 3, 'category' => 'L_FILES', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'files_usergroup', 'cfg_value' => '', 'cfg_desc' => 'L_CFG_FILES_USERGROUP', 'type' => 1, 'category' => 'L_FILES', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'files_perms', 'cfg_value' => '664', 'cfg_desc' => 'L_CFG_FILES_PERMS', 'type' => 1, 'category' => 'L_FILES', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'dir_perms', 'cfg_value' => '775', 'cfg_desc' => 'L_CFG_DIR_PERMS', 'type' => 1, 'category' => 'L_FILES', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'media_ext', 'cfg_value' => 'mkv,avi,mp4', 'cfg_desc' => 'L_CFG_MEDIA_EXT', 'type' => 8, 'category' => 'L_FILES', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'move_only_inapp', 'cfg_value' => 0, 'cfg_desc' => 'L_CFG_MOVE_ONLY_INAPP', 'type' => 3, 'category' => 'TRANSMISSION', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'move_transmission_orphan', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_MOVE_TRANSMISSION_ORPHAN', 'type' => 3, 'category' => 'TRANSMISSION', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'torrent_quality_prefs', 'cfg_value' => '720p,1080p,ANY', 'cfg_desc' => 'L_CFG_TORRENT_QUALITY_PREFS', 'type' => 8, 'category' => 'L_TORRENT', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'torrent_ignore_prefs', 'cfg_value' => 'LATINO,SCREENER', 'cfg_desc' => 'L_CFG_TORRENT_IGNORE_PREFS', 'type' => 8, 'category' => 'L_TORRENT', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'extra_tags', 'cfg_value' => '', 'cfg_desc' => 'L_CFG_EXTRA_TAGS', 'type' => 8, 'category' => 'L_TORRENT', 'public' => 1]);
    $db->insert('config', ['cfg_key' => 'media_language_tag', 'cfg_value' => 'SPANISH,ENGLISH,CASTELLANO,ESPAÑOL', 'cfg_desc' => 'L_CFG_media_language_tag', 'type' => 8, 'category' => 'L_TORRENT', 'public' => 1]);

    return true;
}

/* QUEDAN shows_detauls
 *
 *  */

function update_db($from) {
    global $db;


    if ($from < 2) {
        $query = 'ALTER TABLE wanted add column hashstring VARCHAR NULL';
        $db->query($query);
        $set['version'] = 2;
        $db->update('db_info', $set);
    }

    if ($from < 3) {
        //LIBRARY_MOVIES
        $db->query('ALTER TABLE library_movies add column trailer VARCHAR NULL');
        $db->query('ALTER TABLE library_movies add column file_hash VARCHAR NULL');
        $db->query('ALTER TABLE library_movies add column updated TIMESTAMP DEFAULT 0 NOT NULL');
        //LIBRARY_SHOWS
        $db->query('ALTER TABLE library_shows add column trailer VARCHAR NULL');
        $db->query('ALTER TABLE library_shows add column file_hash VARCHAR NULL');
        $db->query('ALTER TABLE library_shows add column updated TIMESTAMP DEFAULT 0 NOT NULL');
        //TMDB_SEARCH
        $db->query('ALTER TABLE tmdb_search add column updated TIMESTAMP DEFAULT 0 NOT NULL');
        $db->query('DELETE FROM tmdb_search');
        //WANTED
        $db->query('ALTER TABLE wanted add column track_show INTEGER NULL');
        $db->query('ALTER TABLE wanted add column custom_words_ignore VARCHAR NULL');
        $db->query('ALTER TABLE wanted add column custom_words_require VARCHAR NULL');
        $db->query('ALTER TABLE wanted add column exact_title INTEGER NULL');
        //SHOWS_DETAILS
        $db->query("ALTER TABLE shows_details RENAME COLUMN 'update' TO updated");
        //JACKET_SEARCH_*_CACHES
        $db->query("ALTER TABLE jackett_search_movies_cache RENAME COLUMN 'update' TO updated");
        $db->query("ALTER TABLE jackett_search_shows_cache RENAME COLUMN 'update' TO updated");
        //CONFIG
        $db->query('CREATE TABLE IF NOT EXISTS "config" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "cfg_key" VARCHAR NOT NULL,
                    "cfg_value" VARCHAR NOT NULL,
                    "cfg_perms" VARCHAR NULL,
                    "cfg_desc" VARCHAR NULL,
                    "modify" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    UNIQUE (cfg_key)
                )');
        //LIBRARY_HISTORY
        $db->query('CREATE TABLE IF NOT EXISTS "library_history" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "title" VARCHAR NULL,
                    "themoviedb_id" INTEGER NULL,
                    "media_type" VARCHAR NULL,
                    "file_name" VARCHAR NOT NULL,
                    "size" INTEGER NULL,
                    "file_hash" VARCHAR NULL,
                    "season" INTEGER NULL,
                    "episode" INTEGER NULL,
                    "added" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
                )');

        $set['version'] = 3;
        $db->update('db_info', $set);
    }

    if ($from < 4) {
        // TMDB_SEARCH
        $db->query('DROP TABLE "tmdb_search"');
        $db->query('CREATE TABLE IF NOT EXISTS "tmdb_search" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "themoviedb_id" INTEGER NOT NULL,
                    "ilink" VARCHAR NULL,
                    "title" VARCHAR NOT NULL,
                    "clean_title" VARCHAR NULL,
                    "original_title" VARCHAR NULL,
                    "media_type" VARCHAR NULL,
                    "rating" REAL NULL,
                    "popularity" REAL NULL,
                    "elink" VARCHAR NULL,
                    "poster" VARCHAR NULL,
                    "scene" VARCHAR NULL,
                    "trailer" VARCHAR NULL,
                    "lang" VARCHAR NULL,
                    "plot" VARCHAR NULL,
                    "release" VARCHAR NULL,
                    "in_library" INT NULL,
                    "updated" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    UNIQUE(themoviedb_id, media_type)
                )');

        $db->query('ALTER TABLE shows_details add column clean_title VARCHAR NULL');
        $db->query('ALTER TABLE jackett_shows add column clean_title VARCHAR NULL');
        $db->query('ALTER TABLE jackett_movies add column clean_title VARCHAR NULL');
        $db->query('ALTER TABLE library_history add column clean_title VARCHAR NULL');
        $db->query('ALTER TABLE library_shows add column clean_title VARCHAR NULL');
        $db->query('ALTER TABLE library_movies add column clean_title VARCHAR NULL');
        $db->query('ALTER TABLE config add column type VARCHAR NULL');
        $set['version'] = 4;
        $db->update('db_info', $set);
    }

    if ($from < 5) {
        $db->query('ALTER TABLE jackett_shows add column trailer VARCHAR NULL');
        $db->query('ALTER TABLE jackett_movies add column trailer VARCHAR NULL');
        $db->query('ALTER TABLE jackett_shows add column guessed_trailer VARCHAR NULL');
        $db->query('ALTER TABLE jackett_movies add column guessed_trailer VARCHAR NULL');
        $db->query('ALTER TABLE jackett_shows add column guessed_poster VARCHAR NULL');
        $db->query('ALTER TABLE jackett_movies add column guessed_poster VARCHAR NULL');
        $db->query('ALTER TABLE jackett_movies add column guessed_poster VARCHAR NULL');
        $db->query('ALTER TABLE config add column category VARCHAR NULL');
        $db->query('ALTER TABLE config add column public INT NULL');

        $db->insert('config', ['cfg_key' => 'version', 'cfg_value' => 'A77', 'cfg_desc' => '', 'type' => 2, 'category' => '', 'public' => 0]);
        $db->insert('config', ['cfg_key' => 'profile', 'cfg_value' => 0, 'cfg_desc' => '', 'type' => 2, 'category' => '', 'public' => 0]);
        $db->insert('config', ['cfg_key' => 'max_identify_items', 'cfg_value' => 5, 'cfg_desc' => 'L_CFG_MAXID_ITEMS', 'type' => 2, 'category' => '', 'public' => 0]);
        $db->insert('config', ['cfg_key' => 'app_name', 'cfg_value' => 'trackerm', 'cfg_desc' => '', 'type' => 1, 'category' => '', 'public' => 0]);
        $db->insert('config', ['cfg_key' => 'tresults_rows', 'cfg_value' => 2, 'cfg_desc' => 'L_CFG_ROWS', 'type' => 2, 'category' => 'L_DISPLAY', 'public' => 0]);
        $db->insert('config', ['cfg_key' => 'new_cache_expire', 'cfg_value' => 3600, 'cfg_desc' => 'L_CFG_NEW_CACHE_EXPIRE', 'type' => 2, 'category' => 'L_MAIN', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'torrent_media_regex', 'cfg_value' => '/(\.avi|\.mp4|\.mkv)/i', 'cfg_desc' => 'L_CFG_TORRENT_MEDIA_REGEX', 'type' => 1, 'category' => 'L_MAIN', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'db_upd_missing_delay', 'cfg_value' => 864000, 'cfg_desc' => 'L_CFG_UPD_MISSING_DELAY', 'type' => 2, 'category' => 'L_MAIN', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'db_upd_long_delay', 'cfg_value' => 2592000, 'cfg_desc' => 'L_CFG_UPD_LONG_DELAY', 'type' => 2, 'category' => 'L_MAIN', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'want_movies', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_WANT_MOVIES', 'type' => 3, 'category' => 'L_MAIN', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'want_shows', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_WANT_SHOWS', 'type' => 3, 'category' => 'L_MAIN', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'wanted_day_delay', 'cfg_value' => 3000, 'cfg_desc' => 'L_CFG_WANT_DAY_DELAY', 'type' => 2, 'category' => 'L_MAIN', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'charset', 'cfg_value' => 'UTF-8', 'cfg_desc' => 'L_CFG_CHARSET', 'type' => 1, 'category' => 'L_LANG', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'locale', 'cfg_value' => 'en_EN.UTF-8', 'cfg_desc' => 'L_CFG_LOCALE', 'type' => 1, 'category' => 'L_LANG', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'theme', 'cfg_value' => 'default', 'cfg_desc' => 'L_CFG_THEME', 'type' => 1, 'category' => 'L_DISPLAY', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'tresults_columns', 'cfg_value' => 8, 'cfg_desc' => 'L_CFG_COLUMNS', 'type' => 2, 'category' => 'L_DISPLAY', 'public' => 0]);
        $db->insert('config', ['cfg_key' => 'jackett_api_path', 'cfg_value' => '/api/v2.0', 'cfg_desc' => 'L_CFG_JACKETT_API_PATH', 'type' => 2, 'category' => 'L_JACKETT', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'jackett_results', 'cfg_value' => 50, 'cfg_desc' => 'L_CFG_JACKETT_RESULTS', 'type' => 2, 'category' => 'L_JACKETT', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'log_to_syslog', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_LOG_TO_SYSLOG', 'type' => 3, 'category' => 'L_LOGGING', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'log_to_file', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_LOG_TO_FILE', 'type' => 3, 'category' => 'L_LOGGING', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'syslog_level', 'cfg_value' => 'LOG_DEBUG', 'cfg_desc' => 'L_CFG_SYSLOG_LEVEL', 'type' => 1, 'category' => 'L_LOGGING', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'search_cache', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_SEARCH_CACHE', 'type' => 3, 'category' => 'L_SEARCH', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'search_cache_expire', 'cfg_value' => 3600, 'cfg_desc' => 'L_CFG_SEARCH_CACHE_EXPIRE', 'type' => 2, 'category' => 'L_SEARCH', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'wanted_paused', 'cfg_value' => 0, 'cfg_desc' => 'L_CFG_WANTED_PAUSED', 'type' => 3, 'category' => 'L_WANTED', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'cache_images', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_CACHE_IMAGES', 'type' => 3, 'category' => 'L_IMAGES', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'cache_images_path', 'cfg_value' => '/cache/images', 'cfg_desc' => 'L_CFG_CACHE_IMAGES_PATH', 'type' => 1, 'category' => 'L_IMAGES', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'unrar_path', 'cfg_value' => '/usr/bin/unrar', 'cfg_desc' => 'L_CFG_UNRAR_PATH', 'type' => 1, 'category' => 'L_FILES', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'create_movie_folders', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_CREATE_MOVIE_FOLDERS', 'type' => 3, 'category' => 'L_FILES', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'create_shows_season_folder', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_CREATE_SHOWS_SEASON_FOLDER', 'type' => 3, 'category' => 'L_FILES', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'files_usergroup', 'cfg_value' => '', 'cfg_desc' => 'L_CFG_FILES_USERGROUP', 'type' => 1, 'category' => 'L_FILES', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'files_perms', 'cfg_value' => '664', 'cfg_desc' => 'L_CFG_FILES_PERMS', 'type' => 1, 'category' => 'L_FILES', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'dir_perms', 'cfg_value' => '775', 'cfg_desc' => 'L_CFG_DIR_PERMS', 'type' => 1, 'category' => 'L_FILES', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'media_ext', 'cfg_value' => 'mkv,avi,mp4', 'cfg_desc' => 'L_CFG_MEDIA_EXT', 'type' => 8, 'category' => 'L_FILES', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'move_only_inapp', 'cfg_value' => 0, 'cfg_desc' => 'L_CFG_MOVE_ONLY_INAPP', 'type' => 3, 'category' => 'TRANSMISSION', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'move_transmission_orphan', 'cfg_value' => 1, 'cfg_desc' => 'L_CFG_MOVE_TRANSMISSION_ORPHAN', 'type' => 3, 'category' => 'TRANSMISSION', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'torrent_quality_prefs', 'cfg_value' => '720p,1080p,ANY', 'cfg_desc' => 'L_CFG_TORRENT_QUALITY_PREFS', 'type' => 8, 'category' => 'L_TORRENT', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'torrent_ignore_prefs', 'cfg_value' => 'LATINO,SCREENER', 'cfg_desc' => 'L_CFG_TORRENT_IGNORE_PREFS', 'type' => 8, 'category' => 'L_TORRENT', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'extra_tags', 'cfg_value' => '', 'cfg_desc' => 'L_CFG_EXTRA_TAGS', 'type' => 8, 'category' => 'L_TORRENT', 'public' => 1]);
        $db->insert('config', ['cfg_key' => 'media_language_tag', 'cfg_value' => 'SPANISH,ENGLISH,CASTELLANO,ESPAÑOL', 'cfg_desc' => 'L_CFG_media_language_tag', 'type' => 8, 'category' => 'L_TORRENT', 'public' => 1]);
        $set['version'] = 5;
        $db->update('db_info', $set);
    }

    /*
      NEXT UPDATES:
      remove from wanted ignore field, not need
      config: Download button Optional
     */
    /*
      if ($from < 6) {

      $set['version'] = 6;
      $db->update('db_info', $set);
      }
     */
    return true;
}
