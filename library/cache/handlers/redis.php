<?php

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
        $data = $this->redis->get($key."_");

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
