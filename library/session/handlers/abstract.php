<?php
abstract class JaossSessionHandler {
    protected $namespace = null;

    abstract public function init($namespace);
    abstract public function _set($var, $value);
    abstract public function _get($var);
    abstract public function _unset($var);
    abstract public function _isset($var);
    abstract public function _destroy();

    public static function factory($mode) {
        $prefix = ucfirst(strtolower($mode));
        if (class_exists($prefix."SessionHandler")) {
            $class = $prefix."SessionHandler";
            return new $class;
        }
        return null;
    }
}

