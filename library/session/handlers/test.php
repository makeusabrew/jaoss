<?php
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
