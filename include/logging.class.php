<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
Class Log {

    private $console;
    private $cfg;

    public function __construct($cfg) {
        $this->console = false;
        $this->cfg = $cfg;
    }

    public function logged($type, $msg) {
        global $cfg;

        $LOG_TYPE = [
            'LOG_EMERG' => 0, // 	system is unusable
            'LOG_ALERT' => 1, // 	action must be taken immediately
            'LOG_CRIT' => 2, // 	critical conditions
            'LOG_ERR' => 3, //          error conditions
            'LOG_WARNING' => 4, // 	warning conditions
            'LOG_NOTICE' => 5, //	normal, but significant, condition
            'LOG_INFO' => 6, // 	informational message
            'LOG_DEBUG' => 7, //	debug-level message
        ];

        if ($LOG_TYPE[$type] <= $LOG_TYPE[$this->cfg['SYSLOG_LEVEL']]) {

            if ($this->console) {
                if (is_array($msg)) {
                    $msg = var_dump($msg, true);
                }
                echo $this->cfg['app_name'] . " : [" . $type . '] ' . $msg . "\n";
            }

            if ($cfg['LOG_TO_FILE']) {
                $log_file = 'cache/log/trackerm.log';
                if (is_array($msg)) {
                    $msg = print_r($msg, true);
                }
                $content = $this->cfg['app_name'] . " : [" . $type . '] ' . $msg . "\n";
                file_put_contents($log_file, $content, FILE_APPEND);
            }
            if ($cfg['LOG_TO_SYSLOG']) {
                openlog($this->cfg['app_name'] . ' ' . $this->cfg['VERSION'], LOG_NDELAY, LOG_SYSLOG);
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

    public function setConsole($value) {
        if ($value === true || $value === false) {
            $this->console = true;
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

    public function crit($msg) {
        $this->logged('LOG_CRIT', $msg);
    }

    public function alert($msg) {
        $this->logged('LOG_ALERT', $msg);
    }

    public function emerg($msg) {
        $this->logged('LOG_EMERG', $msg);
    }

    public function setStateMsg($msg) {
        global $db;

        $db->addSimpleValue('state_msgs', $msg);
    }

    public function getStateMsgs() {
        global $db;

        $state_msgs = $db->getSimpleValues('state_msgs');
        return isset($state_msgs) ? array_reverse($state_msgs) : false;
    }

}
