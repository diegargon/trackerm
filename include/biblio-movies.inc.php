<?php

/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */

function show_my_movies() {
    global $db;
    
    $page = '';
    
    if (isset($_POST['mult_movies_select']) && !empty($_POST['mult_movies_select'])) {
        submit_ident('movies', $_POST['mult_movies_select']);
    }

    $movies = $db->getTableData('biblio-movies');
    if ($movies == false || isset($_POST['rebuild_movies'])) {
        rebuild_movies($movies);
        $movies = $db->getTableData('biblio-movies');
    }
    
    if ($movies != false) {

        $page .= identify_movies($movies);

        $movies_identifyed = [];

        foreach ($movies as $key => $movie) {
            if (!empty($movie['title'])) {
                $movies_identifyed[$key] = $movie;
            }
        }
        
        $page .= buildTable('L_MOVIES', $movies_identifyed);

    }
        return $page;
    
}
function rebuild_movies($movies) {
    global $cfg, $db;

    $files = findfiles($cfg['MOVIES_PATH'], $cfg['media_ext']);

    $i = 0;

    foreach ($files as $file) {
        if ($movies === false ||
                array_search($file, array_column($movies, 'path')) === false
        ) {

            $mov[$i]['id'] = $i;
            /* File */
            $mov[$i]['file_name'] = $file_name = trim(basename($file));
            /* TITLE  */
            $predictible_title = getMovieTitle($file_name);
            $mov[$i]['predictible_title'] = ucwords($predictible_title);
            $mov[$i]['title'] = '';
            $year = getMovieYear($file_name);
            !empty($year) ? $movies[$i]['year'] = $year : null;
            $mov[$i]['path'] = $file;
            $mov[$i]['tags'] = getMovieTags($file_name);
            $mov[$i]['ext'] = substr($file_name, -3);
            //$mov[$i]['poster'] = '/poster.jpg';

            $i++;
        }
    }

    isset($mov) ? $db->addElements('biblio-movies', $mov) : null;

    return true;
}

function identify_movies($movies) {
    global $LNG;

    $titles = '';

    $i = 0;

    foreach ($movies as $movie) {
        if (empty($movie['title'])) {

            $db_movies = db_search_movies($movie['predictible_title']);
            $results_opt = '';
            if (!empty($db_movies)) {

                foreach ($db_movies as $db_movie) {
                    $year = substr($db_movie['release'], 0, 4);
                    $results_opt .= '<option value="' . $db_movie['id'] . '">' . $db_movie['title'] . ' (' . $year . ')</option>';
                }
                $results_opt .= '<option value="">' . $LNG['L_NOID'] . '</option>';
            }
            $titles .= '<tr><td>' . $movie['predictible_title'] . '</td><td><select name="mult_select[' . $movie['id'] . ']">' . $results_opt . '</select></td></tr>';

            $i++;
            // Maximo 5 peliculas a identidfiar de cada vez, FIXME: configurable y de mejor forma que un break
            if ($i > 5) {
                break;
            }
        }
    }
    if (!empty($titles)) {
        $table = '<form method="post">';
        $table .= '<table>';
        $table .= $titles;
        $table .= '</table>';
        $table .= '<input type="submit" value="' . $LNG['L_IDENTIFY'] . '"/>';
        $table .= '</form>';

        return $table;
    }
    return false;
}

function getMovieTitle($file) {
    /* FIXME Better way */
    /* REGEX */
    /* GET ALL */
    $regex = '/^(?:';
    /* UNTIL */
    $regex .= '(?!\[)'; // [
    $regex .= '(?!\()'; // (
    $regex .= '(?!\()'; // (
    $regex .= '(?!M1080)'; // M1080
    $regex .= '(?!BD1080)'; // BD1080
    $regex .= '(?!HD4K)'; //HD4K        
    $regex .= '(?!Xvid)'; //XviD
    $regex .= '(?!DVD)'; //DVD
    $regex .= '(?!DVDRip)'; //DVDRip        
    $regex .= '(?!HDRip)'; //HDRip
    $regex .= '(?!.mkv)'; //.mkv
    $regex .= '(?!.avi)'; //.avi
    $regex .= '(?!.mp4)'; //.mp4


    /* REGEX TERMINATION */
    $regex .= '.)*/i';


    preg_match($regex, $file, $matches);
    $_title = mb_strtolower($matches[0]);

    $_title = str_replace('.', ' ', $_title);
    $_title = str_replace('_', ' ', $_title);

    return trim($_title);
}

function getMovieTags($file_name) {
    $tags = '';
    if (stripos($file_name, '720p') !== false) {
        $tags .= "[720p]";
    }
    if (stripos($file_name, '1080p') !== false) {
        $tags .= "[1080p]";
    }
    if (stripos($file_name, 'AC3 5.1') !== false) {
        $tags .= "[AC3 5.1]";
    } else if (stripos($file_name, 'AC3') !== false) {
        $tags .= "[AC3]";
    }
    if (stripos($file_name, 'A1080') !== false) {
        $tags .= "[A1080]";
    }
    if (stripos($file_name, 'M1080') !== false) {
        $tags .= "[M1080]";
    }
    if (stripos($file_name, 'BD1080') !== false) {
        $tags .= "[BD1080]";
    }
    $year = getMovieYear($file_name);
    if (!empty($year)) {
        $tags .= '[' . $year . ']';
    }

    return $tags;
}

/* COMMON ? */
function getMovieYear($file_name) {
    $year = '';
    if (preg_match('/\([1-9]{4}\)/', $file_name, $match)) {
        isset($match[0]) ? $year = str_replace('(', '', str_replace(')', '', $match[0])) : null;
    } else if (preg_match('/[1-9]{4}/', $file_name, $match)) {
        isset($match[0]) ? $year = $match[0] : null;
    }

    return $year;
}

