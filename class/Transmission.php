<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Transmission {

    public $valid_conn = false;
    protected $username;
    protected $password;
    protected $token;
    protected $hostname;
    protected $port;
    protected $defaultFields = [
        "id",
        "name",
        "status",
        "doneDate",
        "haveValid",
        "isFinished",
        "totalSize",
        "files",
        "eta",
        "rateDownload",
        "rateUpload",
        "downloadDir",
        "percentDone",
        "hashString",
    ];

    /*
      TR_STATUS_STOPPED = 0,
      TR_STATUS_CHECK_WAIT = 1,
      TR_STATUS_CHECK = 2,
      TR_STATUS_DOWNLOAD_WAIT = 3,
      TR_STATUS_DOWNLOAD = 4,
      TR_STATUS_SEED_WAIT = 5,
      TR_STATUS_SEED = 6
     */
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
    protected $sessionId = false;

    public function __construct($options = []) {
        $this->username = isset($options['trans_username']) ? $options['trans_username'] : null;
        $this->password = isset($options['trans_password']) ? $options['trans_password'] : null;
        $this->token = isset($options['trans_token']) ? $options['trans_token'] : null;
        $this->hostname = isset($options['trans_hostname']) ? $options['trans_hostname'] : 'localhost';
        $this->port = isset($options['trans_port']) ? $options['trans_port'] : 9091;
        return $this->isValidConn();
    }

    public function getAll() {
        $responseArray = $this->callApi('torrent-get', ['fields' => $this->defaultFields]);

        return $responseArray['arguments']['torrents'];
    }

    public function addUrl($url) {
        $arguments = ['filename' => $url];
        return $this->callApi('torrent-add', $arguments);
    }

    public function startID($ids) {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        return $this->callApi('torrent-start', ['ids' => $ids]);
    }

    public function stopID($ids) {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        return $this->callApi('torrent-stop', ['ids' => [1, 5]]);
    }

    public function startAll() {
        $torrents = $this->getAll();
        $ids = [];
        foreach ($torrents as $torrent) {
            $ids[] = $torrent['id'];
        }

        return $this->callApi('torrent-start', ['ids' => $ids]);
    }

    public function stopAll() {
        $torrents = $this->getAll();
        $ids = [];
        foreach ($torrents as $torrent) {
            $ids[] = $torrent['id'];
        }

        return $this->callApi('torrent-stop', ['ids' => $ids]);
    }

    public function removeTorrent($id, $deleteData = false) {
        $arguments = ['ids' => $id];
        if ($deleteData) {
            $arguments['delete-local-data'] = true;
        }
        return $this->callApi('torrent-remove', $arguments);
    }

    public function delete($id) {
        $hashes = [];

        $trans = $this->getAll();

        if (!valid_array($trans)) {
            return false;
        }


        foreach ($trans as $item) {
            if ($item['id'] == $id) {
                $hashes[] = $item['hashString'];
            }
        }


        $ret = $this->removeTorrent($id, true);
        $this->setWantedDelete($hashes);

        return $ret;
    }

    public function isValidConn() {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->transmissionUrl(),
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200 || $httpCode === 409) {
            $this->valid_conn = true;
        } else {
            $this->valid_conn = $httpCode;
        }

        return $this->valid_conn;
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

    public function getStatusName($status) {
        global $LNG;
        return $LNG[$this->status[$status]];
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

    protected function transmissionUrl() {
        $url = sprintf('http://%s:%d/transmission/rpc', $this->hostname, $this->port);
        return $url;
    }

    protected function getSessionId() {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_URL => $this->transmissionUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
        ]);

        $response = curl_exec($curl);
        //var_dump($response);
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        curl_close($curl);

        preg_match('/X-Transmission-Session-Id: ([a-zA-Z0-9]+)/', $headers, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        } else {
            return null;
        }
    }

    protected function buildBaseRequest() {
        if (!$this->sessionId) {
            $this->sessionId = $this->getSessionId();
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->transmissionUrl(),
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-transmission-session-id: ' . $this->sessionId
            ],
        ]);

        if ($this->username) {
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        }

        return $curl;
    }

    protected function callApi($method, $arguments) {
        $request = $this->buildBaseRequest();

        //echo "Session ID {$this->sessionId}<br>";

        curl_setopt($request, CURLOPT_POSTFIELDS, json_encode([
            'method' => $method,
            'arguments' => $arguments
        ]));

        $response = curl_exec($request);

        $httpCode = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);
        if ($httpCode == 409) {
            $this->sessionId = $this->getSessionId();
            return $this->callApi($method, $arguments);
        }


        $responseArray = json_decode($response, true);
        // echo "Llamada {$method}<br/>";
        // echo "Argumentos:<br/>";
        // var_dump($arguments);
        //echo "Respuesta<br/>";


        return $responseArray;
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
