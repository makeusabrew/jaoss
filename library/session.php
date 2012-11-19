<?php
require_once("library/session/handlers/abstract.php");

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

        if ($mode == "autodetect") {
            if (php_sapi_name() == "cli") {
                $mode = "test";
            } else {
                $mode = "default";
            }
        }

        require_once("library/session/handlers/".$mode.".php");

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
