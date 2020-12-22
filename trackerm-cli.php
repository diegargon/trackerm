<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);

//NEED FOR ROOT_PATH and load common and config files FIX with better way
if (file_exists('/etc/trackerm.conf')) {
    require_once('/etc/trackerm.conf');
} else {
    echo "\n" . 'The config file /etc/trackerm.conf is missed, please copy the default file in config.inc.php directory to /etc  and rename it as trackerm.conf and configure the settings' . "\n";
    exit();
}

chdir($cfg['ROOT_PATH']);

require_once('include/common.inc.php');

isset($argv[1]) && $argv[1] == '-console' ? $log->setConsole(true) : null;

require_once('include/trackerm-cli.inc.php');
$log->info("Starting trackerm automatic service...");
$log->debug("Transmission work...");
transmission_scan();

$log->debug("Wanted work...");
wanted_work();

rebuild('movies', $cfg['MOVIES_PATH']);
sleep(1);
rebuild('shows', $cfg['SHOWS_PATH']);

update_trailers();
//UPGRADE
hash_missing(); //3
set_clean(); //4

$log->info("trackerm automatic service finish...");

