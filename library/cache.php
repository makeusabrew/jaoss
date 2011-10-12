<?php
/**
 * simple APC wrapper, for now
 * @todo add different adapters etc
 */
class Cache {
    public static function fetch($key, &$success) {
        return apc_fetch($key, $success);
    }

    public static function store($key, $value, $ttl = 0) {
        return apc_store($key, $value, $ttl);
    }
    
    public static function isEnabled() {
        return (Settings::getValue("site", "cache_enabled", false) == true);
    }
}
