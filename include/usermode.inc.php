<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
require_once('include/common.inc.php');

//while we haven't ui for create users we check $cfg['profiles'] and add to database
foreach ($cfg['profiles'] as $cfgprofile) {
    $profiles['username'] = $cfgprofile;
    $db->upsertItemByField('users', $profiles, 'username');
}

require_once('include/user.inc.php');

require_once('include/session.inc.php');
require_once('include/prefs.inc.php');
loadPrefs();

require_once('include/pages.inc.php');
require_once('include/html.common.php');
require_once('include/library.inc.php');
require_once('include/new_media.inc.php');
