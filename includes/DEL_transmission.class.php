<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
// Actualmente https://github.com/irazasyed/php-transmission-sdk
!defined('IN_WEB') ? exit : true;

class TorrentServer {

    public $trans_conn;
    private $status = [
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
        global $log;

        $this->trans_conn = new Transmission\Client($cfg['trans_hostname'], $cfg['trans_port'], $cfg['trans_username'], $cfg['trans_passwd'], $httpClientBuilder = null);

        //php-transmission-sdk instance not check if the connection can be estabilished we do a request here for check
        /*
        try {
            $this->trans_conn->portTest();
        } catch (Exception $e) {
            $log->err("trans __construct test fail: " . $e->getMessage());
            $this->trans_conn = false;
        }
         * 
         */
        //var_dump($this->trans_conn);
        return $this->trans_conn;
    }

    public function getAll() {
        global $log;

        $array = [];

        try {
            $transfers = $this->trans_conn->get();
        } catch (Exception $e) {
            $log->err("getAll fail: " . $e->getMessage());
            exit(1);
        }
        foreach ($transfers as $key => $transfer) {
            foreach ($transfer as $item_key => $item) {
                $array[$key][$item_key] = $item;
            }
        }

        usort($array, function ($a, $b) {
            return $a['percentDone'] - $b['percentDone'];
        });

        return $array;
    }

    public function addUrl($url, $save_path = null, $options = []) {
        global $log;

        try {
            $ret = $this->trans_conn->addUrl($url, $save_path, $options);
        } catch (Exception $e) {
            $log->err("addUrl fail: " . $e->getMessage());
            return false;
        }
        $this->updateWanted();
        return $ret;
    }

    public function delete($ids) {
        $hashes = [];

        $trans = $this->getAll();

        if (!valid_array($trans)) {
            return false;
        }

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

        if (!valid_array($trans)) {
            return false;
        }

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
        global $log;

        try {
            $ret = $this->trans_conn->stopAll();
        } catch (Exception $e) {
            $log->err("stopAll fail: " . $e->getMessage());
            return false;
        }
        sleep(1);
        $this->updateWanted();
        return $ret;
    }

    public function stop($ids) {
        global $log;

        try {
            $ret = $this->trans_conn->stop($ids);
        } catch (Exception $e) {
            $log->err("stop fail: " . $e->getMessage());
            return false;
        }
        usleep(500000);
        $this->updateWanted();

        return $ret;
    }

    public function startAll() {
        global $log;

        try {
            $ret = $this->trans_conn->startAll();
        } catch (Exception $e) {
            $log->err("startAll fail: " . $e->getMessage());
            return false;
        }
        sleep(1);
        $this->updateWanted();
        return $ret;
    }

    public function start($ids) {
        global $log;

        try {
            $ret = $this->trans_conn->startNow($ids);
        } catch (Exception $e) {
            $log->err("start fail: " . $e->getMessage());
            return false;
        }
        usleep(500000);
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
        if (!valid_array($trans)) {
            return false;
        }
        $wanted_db = $db->getTableData('wanted');

        $hashes = [];
        foreach ($trans as $item) {
            $wanted_item = [];
            $item['status'] == 0 && $item['percentDone'] == 1 ? $status = 8 : $status = $item['status'];

            $hashes[] = $item['hashString'];

            foreach ($wanted_db as $wanted_db_item) {
                if ($wanted_db_item['hashString'] == $item['hashString']) {
                    $wanted_item = $wanted_db_item;
                    break;
                }
            }

            if (valid_array($wanted_item) && ($wanted_item['wanted_status'] != $status)) {
                $update_ary['wanted_status'] = $status;
                $update_ary['id'] = $wanted_item['id'];
                $db->upsertItemByField('wanted', $update_ary, 'id');
            }
        }
        // check if all wanted started are in transmission if not is probably remove from OUTAPP. change status to 10 deleted.

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
                    $log->addStatusMsg('[' . $LNG['L_NOTE'] . '] ' . $LNG['L_TOR_MAN_DEL'] . " {$wanted_item['title']} status: {$wanted_item['wanted_status']}");
                    $wanted_item['wanted_status'] = 10;
                    $db->upsertItemByField('wanted', $wanted_item, 'id');
                }
            }
        }
    }

}
