<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);
define('IN_CLI', true);

if (file_exists('/etc/trackerm.conf')) {
    require_once('/etc/trackerm.conf');
} else {
    echo "\n" . 'The config file /etc/trackerm.conf is missed, please copy the default file in config.inc.php directory '
    . 'to /etc  and rename it as trackerm.conf and configure the settings' . "\n";
    exit();
}

chdir($cfg['ROOT_PATH']);
require_once('includes/common.inc.php');
require_once('includes/trackerm-cli.inc.php');

isset($argv[1]) && $argv[1] == '-console' ? $log->setConsole(true) : null;

if (($c_blocker = getPrefsItem('cli_blocker', true)) && $c_blocker <= 3) {
    setPrefsItem('cli_blocker', ++$c_blocker, true);
    $log->warning("Fail starting TAS reason: blocked ($c_blocker)");

    return false;
}
if (!valid_array($trans)) {
    $log->err("Starting TAS fail: Fail Transmission connection");
    exit(1);
}
transmission_scan();

setPrefsItem('cli_blocker', 1, true);

$log->info("Starting trackerm automatic service...");
wanted_work();
update_things();

setPrefsItem('cli_blocker', 0, true);

$log->info("trackerm automatic service finish...");
