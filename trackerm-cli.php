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
    if (file_exists('root_path')) {
        $ROOT_PATH = trim(file_get_contents('root_path'));
    } else {
        leave('No root path');
    }
}

chdir($ROOT_PATH);
require('include/common.inc.php');
require('include/trackerm-cli.inc.php');

transmission_scan();

wanted_work();

echo "\n";
