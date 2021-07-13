<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

Class Users {

    private $user;

    public function __construct() {
        if (isset($_SESSION['uid']) && $_SESSION['uid'] > 0) {
            $this->user = $this->getProfile($_SESSION['uid']);
            if (empty($this->user['sid']) || $this->user['sid'] != session_id()) {
                $this->user = [];
                $this->user['id'] = -1;
            }
        } else if (!empty($_COOKIE['uid']) && !empty($_COOKIE['sid'])) {
            $this->user = $this->getProfile($_COOKIE['uid']);
            if (!empty($this->user['sid']) && $this->user['sid'] == $_COOKIE['sid']) {
                $_SESSION['uid'] = $_COOKIE['uid'];
                $this->updateSessionId();
            } else {
                $this->user = [];
                $this->user['id'] = -1;
            }
        } else {
            $this->user = [];
            $this->user['id'] = -1;
        }
    }

    public function getId() {
        return $this->user['id'];
    }

    public function getUser() {
        return $this->user;
    }

    public function getEmail() {
        return $this->user['email'] ? $this->user['email'] : false;
    }

    public function getUsername() {
        return $this->user['username'] ? $this->user['username'] : false;
    }

    public function getPassword() {
        return $this->user['password'] ? $this->user['password'] : false;
    }

    public function isAdmin() {
        return empty($this->user['isAdmin']) ? false : true;
    }

    public function getProfiles() {
        global $db;

        $results = $db->select('users');

        return $db->fetchAll($results);
    }

    public function getProfile(int $uid) {
        global $db;

        return $db->getItemById('users', $uid);
    }

    public function checkUser(string $username, string $password) {
        global $db;

        $user_check = $db->getItemByField('users', 'username', $username);

        if (!valid_array($user_check) || empty($user_check['id']) || $user_check['disable'] == 1) {
            return false;
        }
        !empty($password) ? $password_hashed = encrypt_password($password) : $password_hashed = '';

        if (($user_check['password'] == $password_hashed)) {
            $ip = get_user_ip();
            if ($user_check['ip'] != $ip) {
                $db->updateItemById('users', $user_check['id'], ['ip' => $ip]);
            }
            return $user_check['id'];
        }

        return false;
    }

    public function setUser(int $user_id) {
        $_SESSION['uid'] = $user_id;
        $this->user = $this->getProfile($user_id);
        $this->updateSessionId();

        return true;
    }

    private function updateSessionId() {
        global $db, $cfg;

        if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300) {
            setcookie('sid', session_id(), [
                'expires' => time() + $cfg['sid_expire'],
                'secure' => true,
                'samesite' => 'lax',
            ]);
            setcookie('uid', $this->getId(), [
                'expires' => time() + $cfg['sid_expire'],
                'secure' => true,
                'samesite' => 'lax',
            ]);
        } else {
            setcookie("sid", session_id(), time() + $cfg['sid_expire']);
            setcookie("uid", $this->getId(), time() + $cfg['sid_expire']);
        }
        $new_sid = session_id();

        $db->updateItemById('users', $this->getId(), ['sid' => $new_sid]);
        $this->user['sid'] = $new_sid;
    }

}
