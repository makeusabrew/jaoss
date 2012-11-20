<?php
class DefaultSessionHandler extends JaossSessionHandler {
    public function init($namespace) {
        $this->namespace = $namespace;
        if (session_id() == "") {
            session_start();
        }
    }

    public function _set($var, $val) {
        $_SESSION[$this->namespace][$var] = serialize($val);
    }

    public function _get($var) {
        if (isset($_SESSION[$this->namespace][$var])) {
            return unserialize($_SESSION[$this->namespace][$var]);
        }
        return NULL;
    }

    public function _unset($var) {
    	unset($_SESSION[$this->namespace][$var]);
   	}

    public function _isset($var) {
        return isset($_SESSION[$this->namespace][$var]);
    }

    public function _destroy() {
        unset($_SESSION[$this->namespace]);
        if (session_id() != "") {
            session_destroy();
        }
    }
}
