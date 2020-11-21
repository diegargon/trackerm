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
    global $trans, $LNG;

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

    $page .= '<div class="tor_container">';
    foreach ($transfers as $transfer) {
        $page .= '<br/>';
        $page .= '<div class="tor_download">';
        $page .= '<div class="tor_name">' . $transfer['name'] . '</div>';

        $page .= '</div>'; //tor_download


        $page .= '<div class="tor_tags">';
        $page .= '<div class="tor_tag"><span>id: ' . $transfer['id'] . '</div>';
        //empty($transfer['isFinished']) ? $isFinished = 0 : $isFinished = $transfer['isFinished'];
        //$page .= '<div class="tor_tag"><span>isFinish: ' . $isFinished . '</div>';
        $transfer['percentDone'] == 1 ? $percentDone = '100' : $percentDone = (float) $transfer['percentDone'];
        $page .= '<div class="tor_tag"><span>' . $LNG['L_PERCENT'] . ': ' . $percentDone . '%</div>';
        $page .= '<div class="tor_tag"><span>' . $LNG['L_STATUS'] . ': ' . $status[$transfer['status']] . '</div>';
        $page .= '<div class="tor_tag">' . $LNG['L_DESTINATION'] . ': ' . $transfer['downloadDir'] . '</div>';
        $page .= '</div>'; //tor tags

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
