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

$universe = getUniverseData();
$tick = $universe['tick'] + 1;

ships_tick();

if ($tick >= $universe['mining_tick']) {
    $universe_set['mining_tick'] = $tick + $cfg['mining_lapse_tick'];
    mining_tick();
}
if ($tick >= $universe['workers_tick']) {
    $universe_set['workers_tick'] = $tick + $cfg['workers_lapse_tick'];
    workers_tick();
}

$universe_set['tick'] = $tick;
$db->update('universe', $universe_set, ['universe' => 1]);


