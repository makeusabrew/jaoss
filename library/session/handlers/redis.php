<?php
require_once("library/session/storage/redis.php");
class RedisSessionHandler extends JaossSessionHandler {
    public function init($namespace) {
        $config = array();

        RedisSessionStorage::start($config, $namespace);
    }

    public function _set($var, $val) {
        $_SESSION[$var] = $val;
    }

    public function _get($var) {
        if (isset($_SESSION[$var])) {
            return $_SESSION[$var];
        }
        return null;
    }

    public function _unset($var) {
    	unset($_SESSION[$var]);
   	}

    public function _isset($var) {
        return isset($_SESSION[$var]);
    }

    public function _destroy() {
        if (session_id() != "") {
            session_destroy();
        }
    }
}
