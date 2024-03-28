<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

Class Logging {

    private $console;
    private $cfg;

    public function __construct($cfg) {
        $this->console = false;
        $this->cfg = $cfg;
    }

    public function logged($type, $msg) {
        global $cfg;

        $LOG_TYPE = [
            'LOG_EMERG' => 0, // 	UNUSEDsystem is unusable
            'LOG_ALERT' => 1, // 	UNUSED: action must be taken immediately UNUSED
            'LOG_CRIT' => 2, // 	UNUSED: critical conditions
            'LOG_ERR' => 3, //          error conditions
            'LOG_WARNING' => 4, // 	warning conditions
            'LOG_NOTICE' => 5, //	UNUSED: normal, but significant, condition
            'LOG_INFO' => 6, // 	informational message
            'LOG_DEBUG' => 7, //	debug-level message
        ];

        if (isset($LOG_TYPE[$this->cfg['syslog_level']]) && $LOG_TYPE[$type] <= $LOG_TYPE[$this->cfg['syslog_level']]) {

            if ($this->console) {
                if (is_array($msg)) {
                    $msg = var_dump($msg, true);
                }
                echo $this->cfg['app_name'] . " : [" . $type . '] ' . $msg . "\n";
            }

            if ($cfg['log_to_file']) {
                $log_file = 'cache/log/trackerm.log';
                if (is_array($msg)) {
                    $msg = print_r($msg, true);
                }
                $content = '[' . timestamp_to_date(time()) . ']' . $this->cfg['app_name'] . " : [" . $type . '] ' . $msg . "\n";
                if ((file_put_contents($log_file, $content, FILE_APPEND)) === false) {
                    $this->err('Errror writing log to file');
                }
            }
            if ($cfg['log_to_syslog']) {
                if (openlog($this->cfg['app_name'] . ' ' . $this->cfg['version'], LOG_NDELAY, LOG_SYSLOG)) {
                    if (is_array($msg)) {
                        $msg = print_r($msg, true);
                        isset($this->console) ? $this->cfg['app_name'] . " : [" . $type . '] ' . $msg . "\n" : null;
                        syslog($LOG_TYPE[$type], $msg);
                    } else {
                        isset($this->console) ? $this->cfg['app_name'] . " : [" . $type . '] ' . $msg . "\n" : null;
                        syslog($LOG_TYPE[$type], $msg);
                    }
                }
            }
        }
    }

    public function setConsole($value) {
        if ($value === true || $value === false) {
            $this->console = $value;
        } else {
            return false;
        }
    }

    public function debug($msg) {
        $this->logged('LOG_DEBUG', $msg);
    }

    public function info($msg) {
        $this->logged('LOG_INFO', $msg);
    }

    public function notice($msg) {
        $this->logged('LOG_NOTICE', $msg);
    }

    public function warning($msg) {
        $this->logged('LOG_WARNING', $msg);
    }

    public function err($msg) {
        $this->logged('LOG_ERR', $msg);
    }

    public function alert($msg) {
        $this->logged('LOG_ALERT', $msg);
    }

    public function emerg($msg) {
        $this->logged('LOG_EMERG', $msg);
    }

    public function addStatusMsg($msg) {
        global $db;

        $where['type'] = ['value' => 'state'];
        $response = $db->select('log_msgs', null, $where, 'ORDER BY id DESC LIMIT 1 ');
        $state_msg = $db->fetchAll($response);
        if (!isset($state_msg['msg']) || $state_msg['msg'] != $msg) {
            $db->addItem('log_msgs', ['type' => 'state', 'msg' => $msg]);
        }
    }

    public function getStatusMsg() {
        global $db;

        $where['type'] = ['value' => 'state'];

        $response = $db->select('log_msgs', null, $where, 'LIMIT 200');
        $state_msgs = $db->fetchAll($response);

        return !empty($state_msgs) && is_array($state_msgs) ? array_reverse($state_msgs) : false;
    }

    public function clearStateMsgs() {
        global $db;

        $where['type'] = ['value' => 'state'];

        return $db->delete('log_msgs', $where);
    }

}
