<?php

class Session {
    private static $instance = NULL;
    private $namespace = NULL;
    private $handler = NULL;

    public static function getInstance($namespace = NULL) {
        if (self::$instance === NULL) {
            Log::verbose("Instantiating session");
            self::$instance = new Session($namespace);
        }
        return self::$instance;
    }

    public function __construct($namespace = NULL) {
        if ($namespace === NULL) {
            $namespace = Settings::getValue("site.namespace");
        }
        $this->namespace = $namespace;
        try {
            $mode = Settings::getValue("session.handler");
        } catch (CoreException $e) {
            // no mode
            $mode = "default";
        }
        Log::verbose("Initialising session handler [".$mode."]");
        $this->handler = JaossSessionHandler::factory($mode);
        if ($this->handler == null) {
            throw new CoreException("Could not attach session handler");
        }
        $this->handler->init($namespace);
    }

    public function __set($var, $val) {
        $this->handler->_set($var, $val);
    }

    public function __get($var) {
        return $this->handler->_get($var);
    }
    
    public function __unset($var) {
        $this->handler->_unset($var);
   	}

    public function __isset($var) {
        return $this->handler->_isset($var);
    }

    public function destroy() {
        $this->handler->_destroy();
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

abstract class JaossSessionHandler {
    protected $namsepace = null;

    abstract public function init($namespace);
    abstract public function _set($var, $value);
    abstract public function _get($var);
    abstract public function _unset($var);
    abstract public function _isset($var);
    abstract public function _destroy();

    public static function factory($mode) {
        if ($mode == "autodetect") {
            if (php_sapi_name() == "cli") {
                $mode = "test";
            } else {
                $mode = "default";
            }
        }
        $prefix = ucfirst(strtolower($mode));
        if (class_exists($prefix."SessionHandler")) {
            $class = $prefix."SessionHandler";
            return new $class;
        }
        return null;
    }
}

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

class TestSessionHandler extends JaossSessionHandler {
    private $session = null;
    public function init($namespace) {
        $this->namespace = $namespace;
        $this->session[$namespace] = array();
    }

    public function _set($var, $value) {
        $this->session[$this->namespace][$var] = $value;
    }

    public function _get($var) {
        if (isset($this->session[$this->namespace][$var])) {
            return $this->session[$this->namespace][$var];
        }
        return null;
    }

    public function _unset($var) {
    	unset($this->session[$this->namespace][$var]);
   	}

    public function _isset($var) {
        return isset($this->session[$this->namespace][$var]);
    }

    public function _destroy() {
        unset($this->session[$this->namespace]);
    }
}
