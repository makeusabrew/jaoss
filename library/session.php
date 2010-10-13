<?php

class Session {
    private static $instance = NULL;
    private $namespace = NULL;
    public static function getInstance($namespace = NULL) {
        if (self::$instance === NULL) {
            Log::debug("Instantiating session");
            self::$instance = new Session($namespace);
        }
        return self::$instance;
    }

    public function __construct($namespace = NULL) {
        if ($namespace === NULL) {
            $namespace = Settings::getValue("site.namespace");
        }
        
        $this->namespace = $namespace;
        session_start();
    }

    public function __set($var, $val) {
        $_SESSION[$this->namespace][$var] = $val;
    }

    public function __get($var) {
        if (isset($_SESSION[$this->namespace][$var])) {
            // any array stuff or whatever need unserializing?
            return $_SESSION[$this->namespace][$var];
        }
        return NULL;
    }
    
    public function __unset($var) {
    	unset($_SESSION[$this->namespace][$var]);
   	}

    public function __isset($var) {
        return isset($_SESSION[$this->namespace][$var]);
    }

    public function destroy() {
        unset($_SESSION[$this->namespace]);
        self::$instance = NULL;
        session_destroy();
    }
    
    public function setFlash($flash, $value = true) {
        if (!isset($this->_flash_)) {
            $this->_flash_ = array();
        }
        $flashes = $this->_flash_;
        $flashes[$flash] = $value;
        $this->_flash_ = $flashes;
    }

    public function getFlash($flash) {
        $flashes = $this->_flash_;
        if (isset($flashes[$flash])) {
            $value = $flashes[$flash];
            unset($flashes[$flash]);
            $this->_flash_ = $flashes;
            return $value;
        }
        return null;
    }
}
