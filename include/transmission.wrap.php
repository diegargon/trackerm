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
        
}