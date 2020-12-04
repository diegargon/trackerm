<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function create_db() {
    global $newdb;

    //DB_INFO DONE
    $newdb->query('CREATE TABLE IF NOT EXISTS "db_info" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "version" INTEGER NOT NULL,
                    "app_name" VARCHAR NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
                )');

    $query = [
        "app_name" => 'trackerm',
        "version" => 1,
    ];

    $newdb->insert('db_info', $query);

    // USERS DONE
    $newdb->query('CREATE TABLE IF NOT EXISTS "users" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "username" varchar NOT NULL UNIQUE,
                    "password" varchar NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
       )');

    $query = [
        'username' => 'default'
    ];
    $newdb->insert('users', $query);

    // PREFERENCES DONE
    $newdb->query('CREATE TABLE IF NOT EXISTS "preferences" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "uid" INTEGER NOT NULL,
                    "pref_name" VARCHAR NOT NULL,
                    "pref_value" VARCHAR NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    UNIQUE (uid, pref_name)
                )');

    // TMDB_SEARCH DONE
    $newdb->query('CREATE TABLE IF NOT EXISTS "tmdb_search" (
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
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
                )');

    // LOG MSGS DONE
    $newdb->query('CREATE TABLE IF NOT EXISTS "log_msgs" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "type" VARCHAR NOT NULL,
                    "msg" VARCHAR NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
                )');


    // LIBRARY MOVIES
    $newdb->query('CREATE TABLE IF NOT EXISTS "library_movies" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "title" VARCHAR NULL,
                    "themoviedb_id" INTEGER NULL UNIQUE,
                    "file_name" VARCHAR NOT NULL UNIQUE,
                    "predictible_title" VARCHAR NULL,
                    "original_title" VARCHAR NULL,
                    "ilink" VARCHAR NULL,
                    "size" INTEGER NULL,
                    "path" VARCHAR NULL,
                    "tags" VARCHAR NULL,
                    "ext" VARCHAR NULL,
                    "rating" REAL NULL,
                    "popularity" REAL NULL,
                    "scene" VARCHAR NULL,
                    "lang" VARCHAR NULL,
                    "plot" VARCHAR NULL,
                    "title_year" VARCHAR NULL,
                    "poster" VARCHAR NULL,
                    "release" VARCHAR NULL,
                    "master" INTEGER NULL,
                    "added" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
                )');

    //LIBRARY SHOWS
    $newdb->query('CREATE TABLE IF NOT EXISTS "library_shows" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "title" VARCHAR NULL,
                    "themoviedb_id" INTEGER NULL,
                    "file_name" VARCHAR NOT NULL UNIQUE,
                    "predictible_title" VARCHAR NULL,
                    "original_title" VARCHAR NULL,
                    "ilink" VARCHAR NULL,
                    "size" INTEGER NULL,
                    "path" VARCHAR NULL,
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
                    "poster" VARCHAR NULL,
                    "release" VARCHAR NULL,
                    "master" INTEGER NULL,
                    "added" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
                )');

    //JACKET MOVIES DONE
    $newdb->query('CREATE TABLE IF NOT EXISTS "jackett_movies" (
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

    //JACKET SHOWS DONE
    $newdb->query('CREATE TABLE IF NOT EXISTS "jackett_shows" (
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

    //JACKET SEARCH MOVIES CACHE DONE
    $newdb->query('CREATE TABLE IF NOT EXISTS "jackett_search_movies_cache" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "words" VARCHAR NULL,
                    "media_type" VARCHAR NULL,
                    "ids" VARCHAR NULL,
                    "update" INTEGER NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
        )');

    //JACKET SEARCH SHOWS CACHE DONE
    $newdb->query('CREATE TABLE IF NOT EXISTS "jackett_search_shows_cache" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "words" VARCHAR NULL,
                    "media_type" VARCHAR NULL,
                    "ids" VARCHAR NULL,
                    "update" INTEGER NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
        )');

    //WANTED
    $newdb->query('CREATE TABLE IF NOT EXISTS "wanted" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "themoviedb_id" INTEGER NULL,
                    "title" VARCHAR NULL,
                    "season" INTEGER  NULL,
                    "episode" INTEGER NULL,
                    "quality" INTEGER NULL,
                    "ignores" INTEGER NULL,
                    "ignore" INTEGER NULL,
                    "tid" INTEGER NULL,
                    "first_check" INTEGER NULL,
                    "day_check" INTEGER NULL,
                    "last_check" INTEGER NULL,
                    "direct" INTEGER NULL,
                    "wanted_status" INTEGER NULL,
                    "media_type" VARCHAR NULL,
                    "profile" INTEGER NULL,
                    "added" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
        )');
    //SHOWS DETAILS
    $newdb->query('CREATE TABLE IF NOT EXISTS "shows_details" (
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
                    "update" INTEGER NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    UNIQUE (themoviedb_id, season, episode)
                )');

    //Jacket
}

/* QUEDAN shows_detauls
 *
 *  */

function update_db($from) {
    global $newdb;


    if ($from <= 2) {
        //$set['version'] = 3;
        //$newdb->update('db_info', $set);
    }
    if ($from <= 3) {
        //$set['version'] = 4;
        //$newdb->update('db_info', $set);
    }
}
