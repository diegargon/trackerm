<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
define('CLI', true);


$ROOT_PATH = '/var/www/envigo.net/trackerm';

if (empty($ROOT_PATH)) {
    exit();
}

chdir($ROOT_PATH);
require('include/common.inc.php');
require('include/trackerm-cli.inc.php');

inAppMedia();

if ($cfg['MOVE_ONLY_INAPP']) {
    outAppTorrents();
}


wanted_work();
