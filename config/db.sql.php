<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
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

    $query = [
        "app_name" => 'trackerm',
        "version" => 3,
    ];

    $db->insert('db_info', $query);

    // USERS
    $db->query('CREATE TABLE IF NOT EXISTS "users" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "username" varchar NOT NULL UNIQUE,
                    "password" varchar NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
       )');

    $query = [
        'username' => 'default'
    ];
    $db->insert('users', $query);

    // PREFERENCES
    $db->query('CREATE TABLE IF NOT EXISTS "preferences" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "uid" INTEGER NOT NULL,
                    "pref_name" VARCHAR NOT NULL,
                    "pref_value" VARCHAR NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    UNIQUE (uid, pref_name)
                )');
    // CONFIG
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

    // TMDB_SEARCH
    $db->query('CREATE TABLE IF NOT EXISTS "tmdb_search" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "themoviedb_id" INTEGER NOT NULL UNIQUE,
                    "ilink" VARCHAR NULL,
                    "title" VARCHAR NOT NULL,
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
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
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
                    "added" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
                )');

    //JACKET SHOWS
    $db->query('CREATE TABLE IF NOT EXISTS "jackett_shows" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "title" VARCHAR NOT NULL,
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
                    "plot" VARCHAR NULL,
                    "updated" INTEGER NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    UNIQUE (themoviedb_id, season, episode)
                )');

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
    /*
      NEXT UPDATES:

     */
    /*
      if ($from < 4) {
      $set['version'] = 4;
      $db->update('db_info', $set);
      }
     */
    return true;
}
