<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
require('include/common.inc.php');

//while we haven't ui for create users we check $cfg['profiles'] and add to database
foreach ($cfg['profiles'] as $cfgprofile) {
    $profiles['username'] = $cfgprofile;
    $newdb->upsertItemByField('users', $profiles, 'username');
}

require('include/user.inc.php');

require('include/session.inc.php');
require('include/prefs.inc.php');
loadPrefs();

require('include/pages.inc.php');
require('include/html.common.php');
require('include/library.inc.php');
