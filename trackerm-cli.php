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
define('CLI_LOCK', '/var/run/trackerm.lock');

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
$log->info("TrackerM v{$cfg['version']}.{$cfg['db_version']}" . ' Starting trackerm automatic service (' . date("h:i") . ')');

if (!valid_object($trans)) {
    $log->err("Starting TAS fail: Fail Transmission connection");
    exit(1);
}

if (is_locked()) {
    $log->warning("CLI Locked");
    die();
} else {
    register_shutdown_function('unlink', CLI_LOCK);
}

transmission_scan();
wanted_work();
cronjobs();
rebuild('movies', $cfg['MOVIES_PATH']);
sleep(1);
rebuild('shows', $cfg['SHOWS_PATH']);

$log->info("trackerm automatic service finish...");
