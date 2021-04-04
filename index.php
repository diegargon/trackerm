<?php

define('IN_WEB', TRUE);
require('includes/usermode.inc.php');

/*
  $universe = getUniverseData();

  echo "Tick: {$universe['tick']} <br>";
  echo "<p>Hello {$user['username']}</p>";

  echo "<p>Actually you own:</p>";
  echo "<p>Planets:</p>";
  echo show_planets_table($user['id']);
  echo "<p>Ships:</p>";
  echo show_ships_table($user['id']);
 */

$web->render();
$db->close();


