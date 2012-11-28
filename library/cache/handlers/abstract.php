<?php

abstract class CacheHandler {
    protected $fetchHit;

    abstract public function fetch($key);
    abstract public function store($key, $value, $ttl);

    public static function factory($mode) {
        $prefix = ucfirst(strtolower($mode));
        if (class_exists($prefix."CacheHandler")) {
            $class = $prefix."CacheHandler";
            return new $class;
        }
        return null;
    }

    public function init() {
        // no-op
    }

    public function fetchHit() {
        return $this->fetchHit;
    }

    protected function setFetchHit($result) {
        $this->fetchHit = $result;
    }
}
