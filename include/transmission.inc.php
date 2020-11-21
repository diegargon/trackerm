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
    global $trans;

    $status = [
        0 => 'Stopped/Paused',
        1 => 'Queuening for Checking files',
        2 => 'Checking files',
        3 => 'Queuening',
        4 => 'Downloading',
        5 => 'Queue to seed',
        6 => 'Seeding',
        7 => 'No Peers',
    ];

    $transfers = $trans->getAll();

    $page = '';

    $page .= '<div class="tor_container">';
    foreach ($transfers as $transfer) {
        $page .= '<br/>';
        $page .= '<div class="tor_download">';
        $page .= '<div class="tor_name">' . $transfer['name'] . '</div>';
        $page .= '<div class="tor_downdir">' . $transfer['downloadDir'] . '</div>';
        $page .= '</div>'; //tor_download


        $page .= '<div class="tor_tags">';
        $page .= '<div class="tor_tag"><span>id: ' . $transfer['id'] . '</div>';
        empty($transfer['isFinished']) ? $isFinished = 0 : $isFinished = $transfer['isFinished'];
        $page .= '<div class="tor_tag"><span>isFinish: ' . $isFinished . '</div>';
        $transfer['percentDone'] == 1 ? $percentDone = '100' : $percentDone = (float) $transfer['percentDone'];
        $page .= '<div class="tor_tag"><span>percentDone: ' . $percentDone . '%</div>';
        $page .= '<div class="tor_tag"><span>status: ' . $status[$transfer['status']] . '</div>';
        $page .= '</div>';
        /*
          $page .= '<div class="tor_files">';
          foreach ($transfer['files'] as $file) {
          $page .= '<div class="tor_file">' . $file['name'] . '</div>';
          }
          $page .= '</div>'; //tor_files
         */
    }

    $page .= '</div>'; //tor_container

    return $page;
}
