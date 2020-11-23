<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
define('CLI', true);


$ROOT_PATH = '';

if (empty($ROOT_PATH)) {
    exit();
}

chdir($ROOT_PATH);
require('include/common.inc.php');
require('include/trackerm-cli.inc.php');


$torrents_db = $db->getTableData('transmission');
$transfers = $trans->getAll();


if ($cfg['MOVE_ONLY_INAPP'] && !empty($torrents_db)) {
    echo "\n INAPPI:1 No Torrents Finished Found ";
    leave();
}

$tors = getRightTorrents($transfers, $torrents_db);

if ($tors == false) {
    echo "\n No valid torrents found";
    leave();
}

//var_dump($tors);

foreach ($tors as $tor) {
    $item = [];

    $item['tid'] = $tor['id'];
    $item['dirname'] = $tor['name'];
    $item['title'] = getFileTitle($item['dirname']);
    $item['status'] = $tor['status'];
    $item['media_type'] = getMediaType($item['dirname']);
    if ($item['media_type'] == 'shows') {
        $SE = getFileEpisode($item['dirname']);
        (strlen($SE['season']) == 1) ? $item['season'] = 0 . $SE['season'] : $item['season'] = $SE['season'];
        (strlen($SE['chapter']) == 1) ? $item['episode'] = 0 . $SE['chapter'] : $item['episode'] = $SE['chapter'];
    } else {
        $item['season'] = '';
        $item['episode'] = '';
    }
    //echo "\n" . $item['tid'] . ':' . $item['status'] . ':' . $item['title'] . ':' . $item['media_type'] . ':S' . $item['season'] . 'E' . $item['episode'] . "\n";

    if ($item['media_type'] == 'movies') {
        echo "\n Movie detected begin moving" . $item['title'];
        moveMovie($item, $trans);
    } else if ($item['media_type'] == 'shows') {
        echo "\n Show detected begin moving" . $item['title'] . ' S' . $item['season'] . 'E' . $item['episode'];
        moveShow($item, $trans);
    }
}

function leave($msg = false) {
    echo "\n Exit Called";
    !empty($msg) ? print $msg . "\n" : print "\n";

    exit();
}
