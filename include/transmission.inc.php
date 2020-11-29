<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 * https://github.com/transmission/transmission/blob/master/extras/rpc-spec.txt
 */
function page_transmission() {
    global $trans, $LNG, $filter;

    if (isset($_POST['tid'])) {
        $tid = $filter->postInt('tid');
    }

    isset($_POST['start_all']) ? $trans->startAll() : null;
    isset($_POST['stop_all']) ? $trans->stopAll() : null;

    if (!empty($tid)) {
        isset($_POST['start']) ? $trans->start($tid) . sleep(1) : null;
        isset($_POST['stop']) ? $trans->stop($tid) . sleep(1) : null;
        isset($_POST['delete']) ? $trans->delete($tid) . sleep(1) : null;
    }

    $status = [
        0 => $LNG['L_STOPPED'],
        1 => $LNG['L_QUEUENING_CHECKING'],
        2 => $LNG['L_CHECKING_FILES'],
        3 => $LNG['L_QUEUENING'],
        4 => $LNG['L_DOWNLOADING'],
        5 => $LNG['L_QUEUENING_TO_SEED'],
        6 => $LNG['L_SEEDING'],
        7 => $LNG['L_NO_PEERS'],
    ];

    $transfers = $trans->getAll();

    $page = '';
    $tdata['body'] = '';

    foreach ($transfers as $transfer) {
        $tdata['body'] .= getTpl('transmission-row', array_merge($transfer, $LNG, $status));
    }

    $page .= getTpl('transmission-body', array_merge($tdata, $LNG));

    return $page;
}
