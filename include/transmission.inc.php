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
        $transfer['status'] == 0 ? $tdata['show_start'] = 1 : $tdata['show_start'] = '';
        $transfer['status'] != 0 && $transfer['status'] < 8 ? $data['show_stop'] = 1 : $tdata['show_stop'] = '';

        $tdata['status_name'] = $trans->getStatusName($transfer['status']);
        $transfer['percentDone'] == 1 ? $tdata['percent'] = '100' : $tdata['percent'] = ((float) $transfer['percentDone']) * 100;
        $tdata['body'] .= getTpl('transmission-row', array_merge($transfer, $tdata, $LNG));
    }

    $page .= getTpl('transmission-body', array_merge($tdata, $LNG));

    return $page;
}
