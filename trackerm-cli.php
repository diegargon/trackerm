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
require_once('includes/climode.inc.php');

isset($argv[1]) && $argv[1] == '-console' ? $log->setConsole(true) : null;
$log->info("TrackerM v{$cfg['version']}.{$cfg['db_version']}" . ' Starting trackerm automatic service...');

if (($c_blocker = $prefs->getPrefsItem('cli_blocker')) && $c_blocker <= 3) {
    $prefs->setPrefsItem('cli_blocker', ++$c_blocker);
    $log->warning("Fail starting TAS reason: blocked ($c_blocker)");

    return false;
}
if (!valid_object($trans)) {
    $log->err("Starting TAS fail: Fail Transmission connection");
    exit(1);
}

transmission_scan();
$prefs->setPrefsItem('cli_blocker', 1);
wanted_work();
cronjobs();
rebuild('movies', $cfg['MOVIES_PATH']);
sleep(1);
rebuild('shows', $cfg['SHOWS_PATH']);
$prefs->setPrefsItem('cli_blocker', 0);

$log->info("trackerm automatic service finish...");
