<?php

class ApcCacheHandler extends CacheHandler {
    public function fetch($key) {
        $success = false;

        $data = apc_fetch($key, $success);

        $this->setFetchHit($success);

        return $data;
    }

    public function store($key, $value, $ttl) {
        return apc_store($key, $value, $ttl);
    }
}
