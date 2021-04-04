<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
define('IN_WEB', TRUE);
chdir('/var/www/envigo.net/Universe');
require('includes/climode.inc.php');

function planet_in_range($table, $spacer, $planet) {
    global $db;


    $query = 'SELECT x,y,z FROM ' . $table . ' WHERE ';
    $query .= 'x BETWEEN ' . ($planet['x'] - $spacer) . ' AND ' . ($planet['x'] + $spacer) . ' AND ';
    $query .= 'y BETWEEN ' . ($planet['y'] - $spacer) . ' AND ' . ($planet['y'] + $spacer) . ' AND ';
    $query .= 'z BETWEEN ' . ($planet['z'] - $spacer) . ' AND ' . ($planet['z'] + $spacer);
    $results = $db->query($query);
    $result_row = $db->fetch($results);
    if ($result_row) {
        return true;
    } else {
        return false;
    }
}

function create_suns() {
    global $galaxy_tpl, $db;

    $table = 'planets7';
    $num_galaxys = 5000000;

    $min_x = 5000000;
    $min_y = 5000000;
    $min_z = 5000000;
    $max_x = 6000000;
    $max_y = 6000000;
    $max_z = 6000000;

    $dups = 0;
    $added = 0;
    for ($i = 1; $i < $num_galaxys; $i++) {
        $sun['x'] = random_int($min_x, $max_x);
        $sun['y'] = random_int($min_y, $max_y);
        $sun['z'] = random_int($min_z, $max_z);

        $galaxy_spacer = $galaxy_tpl['galaxy_min_spacer'];


        if (planet_in_range($table, $galaxy_spacer, $sun)) {
            //$num_galaxys++;
            $dups++;
            //echo "+";
            //usleep(500000);
            continue;
        } else {
            //echo "Creando sol en " . $sun['x'] . ':' . $sun['y'] . ':' . $sun['z'] . "\n";
            $db->query("INSERT INTO $table  (sun, x, y, z) VALUES (1, {$sun['x']}, {$sun['y']}, {$sun['z']})");
            /*
              $num_planets = rand($galaxy_tpl['galaxy_min_planets'], $galaxy_tpl['galaxy_max_planets']);
              $c = $num_planets;

              $planet_spacer = $galaxy_tpl['planet_min_spacer'];
              for ($b = 0; $b < $num_planets; $b++) {

              $planet['x'] = random_int($sun['x'] + $planet_spacer, $sun['x'] + $galaxy_tpl['galaxy_maxsize_from_sun']);
              $planet['y'] = random_int($sun['y'] + $planet_spacer, $sun['y'] + $galaxy_tpl['galaxy_maxsize_from_sun']);
              $planet['z'] = random_int($sun['z'] + $planet_spacer, $sun['z'] + $galaxy_tpl['galaxy_maxsize_from_sun']);
              if (planet_in_range($table, $planet_spacer, $planet)) {
              $num_planets++;
              echo "*\n";
              sleep(1);
              continue;
              } else {
              echo "Creando planeta en " . $planet['x'] . ':' . $planet['y'] . ':' . $planet['z'] . "\n";
              $db->query("INSERT INTO $table  (sun, x, y, z) VALUES (0, {$planet['x']}, {$planet['y']}, {$planet['z']})");
              $c++;
              }
              sleep(1);
              }
             *
             */
            $added++;
        }
        //sleep(1);
    }
    echo "Added $added Dups $dups\n";
}

create_suns();


