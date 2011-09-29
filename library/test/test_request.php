<?php
class TestRequest extends JaossRequest {
    protected $postFields = array();
    protected $getFields = array();
    protected $folder_base = '/';

    public function __construct() {
        $this->sapi = php_sapi_name();
        $this->method = 'GET';  // assume get by default
    }

    public function setProperties($params = array()) {
        if ($this->sapi != "cli") {
            Log::debug("attempting to set request properties via non CLI server API!");
            return false;
        }
        $allowed_params = array("folder_base", "base_href", "url", "query_string", "method", "ajax", "referer");
        foreach ($allowed_params as $param) {
            if (isset($params[$param])) {
                $this->$param = $params[$param];
            }
        }
        return $this;
    }

    public function setMethod($method) {
        $this->method = $method;
        return $this;
    }

    public function setPost($fields) {
        $this->postFields = $fields;
        return $this;
    }

    public function setGet($fields) {
        $this->getFields = $fields;
        return $this;
    }

    public function setParams($fields) {
        switch ($this->method) {
            case 'GET':
                return $this->setGet($fields);
            case 'POST':
                return $this->setPost($fields);
            default:
                throw new CoreException('Unknown request method ['.$this->method.']');
        }
    }

    public function setReferer($referer) {
        $this->referer = $referer;
        return $this;
    }

    public function getVar($var, $default = null) {
        if (isset($this->postFields[$var])) {
            return $this->postFields[$var];
        }
        if (isset($this->getFields[$var])) {
            return $this->getFields[$var];
        }
        return $default;
    }

    public function getPost() {
        return $this->postFields;
    }

    public function getGet() {
        return $this->getFields;
    }

    public function getIp() {
        // override as this will be empty otherwise
        return "127.0.0.2";
    }

    public function reset() {
        $this->postFields = array();
        $this->getFields = array();
        $this->response = null;
        $this->method = null;
        $this->referer = null;
        PathManager::reloadPaths();
        return $this;
    }
}
