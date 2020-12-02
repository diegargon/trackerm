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
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )');

    $query = [
        "app_name" => 'trackerm',
        "version" => 1,
    ];

    $db->insert('db_info', $query);

    // PREFERENCES

    $db->query('CREATE TABLE IF NOT EXISTS "preferences" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "username" INTEGER NOT NULL,
                    "password" VARCHAR NULL,
                    "theme" VARCHAR NULL,
                    "tresults_rows" INTEGER NULL,
                    "tresults_columns" INTEGER NULL,
                    "max_identify_items" INTEGER NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )');

    $query = [
        "username" => 'default',
    ];


    $db->insert('preferences', $query);

    // TMDB_SEARCH

    $db->query('CREATE TABLE IF NOT EXISTS "tmdb_search" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "themoviedb_id" INTEGER NOT NULL,
                    "ilink" VARCHAR NULL,
                    "title" VARCHAR NOT NULL,
                    "original_title" VARCHAR NULL,
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
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )');


    // STATE MSGS

    $db->query('CREATE TABLE IF NOT EXISTS "state_msgs" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "msg" VARCHAR NOT NULL,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )');


    // LIBRARY MOVIES

    $db->query('CREATE TABLE IF NOT EXISTS "library_movies" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "themoviedb_id" INTEGER NULL,
                    "file_name" VARCHAR NOT NULL,
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
                    "master" INTEGER NULL,
                    "added" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )');

    //LIBRARY SHOWS

    $db->query('CREATE TABLE IF NOT EXISTS "library_shows" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "themoviedb_id" INTEGER NULL,
                    "file_name" VARCHAR NOT NULL,
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
                    "master" INTEGER NULL,
                    "added" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )');

    //JACKET MOVIES
    $db->query('CREATE TABLE IF NOT EXISTS "jackett_movies" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "title" VARCHAR NOT NULL,
                    "guid" VARCHAR NOT NULL,
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
                    "added" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )');

    //JACKET SHOWS
    $db->query('CREATE TABLE IF NOT EXISTS "jackett_shows" (
                    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    "title" VARCHAR NOT NULL,
                    "guid" VARCHAR NOT NULL,
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
                    "added" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )');
}

/* QUEDAN shows_detauls jacket_search_* que tienen arrays */

function update_db($from) {
    global $db;


    if ($from <= 2) {
        //$set['version'] = 3;
        //$db->update('db_info', $set);
    }
    if ($from <= 3) {
        //$set['version'] = 4;
        //$db->update('db_info', $set);
    }
}
