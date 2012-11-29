<?php
/**
 * be aware that the redis handler json_encodes data on storing it
 * and json_decodes it as an array when fetching.
 *
 * This means you should only really be caching simple types - if you
 * need to cache objects, toArray them first and then re-populate them
 * with a fromArray method upon fetching data from the cache
 */

class RedisCacheHandler extends CacheHandler {
    protected $redis;

    public function init() {
        $config = array(
            "host" => Settings::getValue("redis", "host", "localhost"),
            "port" => Settings::getValue("redis", "port", "6379"),
        );

        $this->redis = new \Predis\Client($config); 
    }

    public function fetch($key) {
        $data = $this->redis->get($key);

        $success = $data !== null;

        $this->setFetchHit($success);

        if ($success) {
            return json_decode($data, true);
        }

        return null;
    }

    public function store($key, $value, $ttl) {
        $result = $this->redis->setex($key, $ttl, json_encode($value));
    }
}
