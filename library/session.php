<?php

class Session {
    private static $instance;
    private $namespace = NULL;
    public static function getInstance($namespace = NULL) {
        if (!is_a(self::$instance, 'Session')) {
                self::$instance = new Session($namespace);
        }
        return self::$instance;
    }

    public function __construct($namespace = NULL) {
        if ($namespace === NULL) {
            // get from site settings...
        }
        $this->namespace = $namespace;
        if (session_id() == "") {
            session_start();
        }
        $_SESSION[$this->namespace] = array();
    }

    public function __set($var, $val) {
        $_SESSION[$this->namespace][$var] = $val;
    }

    public function __get($var) {
        if (isset($_SESSION[$this->namespace][$var])) {
            // any array stuff or whatever need unserializing?
            return $_SESSION[$this->namespace][$var];
        }
    }

    public function destroy() {
        unset($_SESSION[$this->namespace]);
        self::$instance = NULL;
        session_destroy();
    }
}
