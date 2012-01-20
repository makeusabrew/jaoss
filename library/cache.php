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
        try {
            return apc_store($key, $value, $ttl);
        } catch (ErrorException $e) {
            Log::warn("Caught APC error, swallowing. [".$e->getMessage()."]");
        }
    }
    
    public static function isEnabled() {
        return (Settings::getValue("site", "cache_enabled", false) == true);
    }
}
