<?php

class Session {
    private static $instance = NULL;
    private $namespace = NULL;
    public static function getInstance($namespace = NULL) {
        if (self::$instance === NULL) {
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
        Log::debug("setting flash [".$flash."] with val [".$value."]");
        $_SESSION[$this->namespace]["_flash_"][$flash] = $value;
    }

    public function getFlash($flash) {
        Log::debug("looking for flash [".$flash."]");
        if (isset($_SESSION[$this->namespace]["_flash_"][$flash])) {
            Log::debug("found flash [".$flash."]");
            $flash = $_SESSION[$this->namespace]["_flash_"][$flash];
            unset($_SESSION[$this->namespace]["_flash_"][$flash]);
            return $flash;
        }
        Log::debug("Could not find flash [".$flash."]");
        return false;
    }
}
