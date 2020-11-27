<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
// Actualmente https://github.com/irazasyed/php-transmission-sdk

class TorrentServer {

    public $trans_conn;

    public function __construct($cfg) {
        $this->trans_conn = new Transmission\Client($cfg['trans_hostname'], $cfg['trans_port'], $cfg['trans_username'], $cfg['trans_passwd'], $httpClientBuilder = null);
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

    public function addUrl($url) {
        //checking if work without rawurldecode($url)
        return $this->trans_conn->addUrl($url);
    }

    public function delete($ids) {
        global $db;

        $trans_db = $db->getTableData('transmission');
        foreach ($ids as $id) {
            foreach ($trans_db as $trans) {
                if ($trans['tid'] == $id) {
                    $db->deleteByFieldMatch('transmission', 'tid', $trans['tid']);
                }
            }
        }
        return $this->trans_conn->remove($ids, true);
    }

    public function stopAll() {
        $ret = $this->trans_conn->stopAll();
        //sleep(1);
        $this->updateWanted();
        return $ret;
    }

    public function stop($ids) {
        $this->updateWantedToStatus($ids, 3);
        return $this->trans_conn->stop($ids);
    }

    public function startAll() {
        $ret = $this->trans_conn->startAll();
        //sleep(1);
        $this->updateWanted();
        return $ret;
    }

    public function start($ids) {
        $ret = $this->trans_conn->startNow($ids);
        //sleep(1);
        $this->updateWanted();
        return $ret;
    }

    private function updateWantedToStatus($ids, $status) {
        //TODO
    }

    private function updateWanted() {
        //TODO check torrents ids and update wanted
    }

}
