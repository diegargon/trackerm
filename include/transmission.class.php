<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
// Actualmente https://github.com/irazasyed/php-transmission-sdk
!defined('IN_WEB') ? exit : true;

class TorrentServer {

    public $trans_conn;
    public $status = [
        // Torrent Status
        0 => 'L_STOPPED',
        1 => 'L_VERIFYING',
        2 => 'L_CHECKING_FILES',
        3 => 'L_QUEUENING',
        4 => 'L_DOWNLOADING',
        5 => 'L_QUEUENING_TO_SEED',
        6 => 'L_SEEDING',
        7 => 'L_NO_PEERS',
        // Custom Status used in wanted
        8 => 'L_COMPLETED', //tor sttoped 0 y percenDone 1
        9 => 'L_MOVED',
        10 => 'L_DELETED',
    ];

    public function __construct($cfg) {
        return $this->trans_conn = new Transmission\Client($cfg['trans_hostname'], $cfg['trans_port'], $cfg['trans_username'], $cfg['trans_passwd'], $httpClientBuilder = null);
    }

    public function getAll() {
        $array = [];
        $transfers = $this->trans_conn->get();
        foreach ($transfers as $key => $transfer) {
            foreach ($transfer as $item_key => $item) {
                $array[$key][$item_key] = $item;
            }
        }
        return $array;
    }

    public function addUrl($url, $save_path = null, $options = []) {
        $ret = $this->trans_conn->addUrl($url, $save_path, $options);
        sleep(1);
        $this->updateWanted();
        return $ret;
    }

    public function delete($ids) {
        $hashes = [];

        $trans = $this->getAll();
        foreach ($ids as $id) {
            foreach ($trans as $item) {
                if ($item['id'] == $id) {
                    $hashes[] = $item['hashString'];
                }
            }
        }

        $ret = $this->trans_conn->remove($ids, true);
        $this->setWantedDelete($hashes);

        return $ret;
    }

    public function deleteHashes($hashes) {
        $ids = [];
        $trans = $this->getAll();
        foreach ($hashes as $hash) {
            foreach ($trans as $item) {
                if ($item['hashString'] == $hash) {
                    $ids[] = $item['id'];
                }
            }
        }
        if (count($ids) > 0) {
            $this->delete($ids);
        }
    }

    public function stopAll() {
        $ret = $this->trans_conn->stopAll();
        sleep(1);
        $this->updateWanted();
        return $ret;
    }

    public function stop($ids) {
        $ret = $this->trans_conn->stop($ids);
        sleep(1);
        $this->updateWanted();

        return $ret;
    }

    public function startAll() {
        $ret = $this->trans_conn->startAll();
        sleep(1);
        $this->updateWanted();
        return $ret;
    }

    public function start($ids) {
        $ret = $this->trans_conn->startNow($ids);
        sleep(1);
        $this->updateWanted();
        return $ret;
    }

    public function getStatusName($status) {
        global $LNG;
        return $LNG[$this->status[$status]];
    }

    public function updateWanted() {
        global $db;

        $trans = $this->getAll();

        $hashes = [];
        foreach ($trans as $item) {

            $item['status'] == 0 && $item['percentDone'] == 1 ? $status = 8 : $status = $item['status'];

            $hashes[] = $item['hashString'];

            $wanted_item = $db->getItemByField('wanted', 'hashString', $item['hashString']);

            if ($wanted_item && ($wanted_item['wanted_status'] != $status)) {
                $update_ary['wanted_status'] = $status;
                $update_ary['id'] = $wanted_item['id'];
                $db->upsertItemByField('wanted', $update_ary, 'id');
            }
        }
        // check if all wanted started are in transmission if not is probably remove from OUTAPP. change status to 10 deleted.
        $wanted_db = $db->getTableData('wanted');

        foreach ($wanted_db as $wanted_item) {
            if (($wanted_item['wanted_status'] > 1) &&
                    ($wanted_item['wanted_status'] < 9) && !in_array($wanted_item['hashString'], $hashes)) {
                $update_ary['wanted_status'] = 10;
                $update_ary['id'] = $wanted_item['id'];
                $db->upsertItemByField('wanted', $update_ary, 'id');
            }
        }
    }

    private function setWantedDelete($hashes) {
        global $db, $log, $LNG;
        foreach ($hashes as $hash) {
            $wanted_item = $db->getItemByField('wanted', 'hashString', $hash);
            if ($wanted_item !== false) {
                if (!empty($wanted_item['direct'] == 1)) {
                    $db->deleteItemById('wanted', $wanted_item['id']);
                } else if (empty($wanted_item['direct']) && ($wanted_item['wanted_status'] != 9)) {
                    $log->addStateMsg('[' . $LNG['L_NOTE'] . '] ' . $LNG['L_TOR_MAN_DEL'] . " {$wanted_item['title']} status: {$wanted_item['wanted_status']}");
                    $wanted_item['wanted_status'] = 10;
                    $db->upsertItemByField('wanted', $wanted_item, 'id');
                }
            }
        }
    }

}
