<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 * https://github.com/transmission/transmission/blob/master/extras/rpc-spec.txt
 */
!defined('IN_WEB') ? exit : true;

function page_transmission() {
    global $trans, $LNG, $filter;

    $tid = $filter->postInt('tid');

    isset($_POST['start_all']) ? $trans->startAll() : null;
    isset($_POST['stop_all']) ? $trans->stopAll() : null;

    if (!empty($tid)) {
        isset($_POST['start']) ? $trans->start($tid) . sleep(1) : null;
        isset($_POST['stop']) ? $trans->stop($tid) . sleep(1) : null;
        isset($_POST['delete']) ? $trans->delete($tid) . sleep(1) : null;
    }

    $transfers = $trans->getAll();

    $page = '';
    $tdata['body'] = '';

    foreach ($transfers as $transfer) {
        $transfer['status_name'] = $trans->getStatusName($transfer['status']);
        $tdata['body'] .= getTpl('transmission-row', array_merge($transfer, $LNG));
    }

    $page .= getTpl('transmission-body', array_merge($tdata, $LNG));

    return $page;
}
