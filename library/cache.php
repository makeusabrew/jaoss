<?php
require_once("library/cache/handlers/abstract.php");

class Cache {
    private static $instance;
    private $handler;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Cache();
        }
        return self::$instance;
    }

    public function __construct() {
        $handler = Settings::getValue("cache", "handler");

        require_once("library/cache/handlers/".$handler.".php");

        Log::verbose("Initialising cache handler [".$handler."]");

        $this->handler = CacheHandler::factory($handler);
        if ($this->handler == null) {
            throw new CoreException("Could not attach cache handler");
        }
    }

    public function store($key, $value, $ttl = 0) {
        return $this->handler->store($key, $value, $ttl);
    }

    public function fetch($key) {
        $value = $this->handler->fetch($key);
        if ($this->handler->fetchHit()) {
            StatsD::increment("cache.hit");
        } else {
            StatsD::increment("cache.miss");
        }
        return $value;
    }

    public function fetchHit($key = null) {
        return $this->handler->fetchHit($key);
    }
    
    public static function isEnabled() {
        return (Settings::getValue("site", "cache_enabled", false) == true);
    }
}
