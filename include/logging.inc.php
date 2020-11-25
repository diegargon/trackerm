<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function logged($type, $msg) {
    global $cfg;

    $LOG_TYPE = [
        'LOG_EMERG' => 0, // 	system is unusable
        'LOG_ALERT' => 1, // 	action must be taken immediately
        'LOG_CRIT' => 2, // 	critical conditions
        'LOG_ERR' => 3, //      error conditions
        'LOG_WARNING' => 4, // 	warning conditions
        'LOG_NOTICE' => 5, //	normal, but significant, condition
        'LOG_INFO' => 6, // 	informational message
        'LOG_DEBUG' => 7, //	debug-level message
    ];

    if ($LOG_TYPE[$type] <= $LOG_TYPE[$cfg['SYSLOG_LEVEL']]) {
        openlog($cfg['app_name'] . ' ' . $cfg['VERSION'], LOG_NDELAY, LOG_SYSLOG);
        syslog($LOG_TYPE[$type], $msg);
    }
}

function log_debug($msg) {
    logged('LOG_DEBUG', $msg);
}

function log_info($msg) {
    logged('LOG_INFO', $msg);
}

function log_notice($msg) {
    logged('LOG_NOTICE', $msg);
}

function log_warning($msg) {
    logged('LOG_WARNING', $msg);
}

function log_err($msg) {
    logged('LOG_ERR', $msg);
}

function log_crit($msg) {
    logged('LOG_CRIT', $msg);
}

function log_($msg) {
    logged('LOG_ALERT', $msg);
}

function log_emerg($msg) {
    logged('LOG_EMERG', $msg);
}
