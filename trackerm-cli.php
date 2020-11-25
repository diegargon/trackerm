<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
define('CLI', true);

//NEED FOR ROOT_PATH and load common and config files FIX with better way
if (file_exists('/etc/trackerm.conf')) {
    require('/etc/trackerm.conf');
} else {
    echo "\n" . 'The config file /etc/trackerm.conf is missed, please copy the default file in config.inc.php directory to /etc  and rename it as trackerm.conf and configure the settings' . "\n";
    exit();
}

chdir($cfg['ROOT_PATH']);

require('include/common.inc.php');
require('include/trackerm-cli.inc.php');

echo "\nMoving work...";
transmission_scan();

echo "\nWanted work...";
wanted_work();

//FIXME: Error glob_recursive file_search
//rebuild('movies', $cfg['MOVIES_PATH']);
//rebuild('shows', $cfg['SHOWS_PATH']);

echo "\n";
