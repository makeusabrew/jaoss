<?php
class TestRequest extends JaossRequest {
    protected $postFields = array();
    protected $getFields = array();

    public function setParams($params = array()) {
        if (!$this->sapi == "cli") {
            Log::debug("attempting to set request params via non CLI server API!");
            return false;
        }
        $allowed_params = array("folder_base", "base_href", "url", "query_string", "method", "ajax", "referer");
        foreach ($allowed_params as $param) {
            if (isset($params[$param])) {
                $this->$param = $params[$param];
            }
        }
        return true;
    }

    public function setMethod($method) {
        $this->method = $method;
    }

    public function setPost($fields) {
        $this->postFields = $fields;
    }

    public function getVar($var) {
        if (isset($this->postFields[$var])) {
            return $this->postFields[$var];
        }
        if (isset($this->getFields[$var])) {
            return $this->getFields[$var];
        }
        return null;
    }

    public function getPost() {
        return $this->postFields;
    }

    public function getGet() {
        return $this->getFields;
    }

    public function reset() {
        $this->postFields = array();
        $this->getFields = array();
        $this->response = null;
        $this->method = null;
    }
}
