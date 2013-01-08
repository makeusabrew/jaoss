<?php

class CurlResponse {
    protected $info = array();
    protected $body;
    
    public function setInfo(array $info = array()) {
        $this->info = $info;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    public function getBody() {
        return $this->body;
    }

    public function getStatusCode() {
        return $this->info['http_code'];
    }

    public function getStatusClass() {
        $class = substr($this->getStatusCode(), 0, 1);
        return $class."xx";
    }
}
