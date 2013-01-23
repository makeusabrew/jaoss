<?php
require_once("library/exception/curl.php");

class CurlRequest {
    protected $ch;
    protected $method;
    protected $url;
    protected $params = array();

    public function __construct() {
        $this->ch = curl_init();
        $this->setOpt(CURLOPT_RETURNTRANSFER, true)
             ->setOpt(CURLOPT_HEADER, true);
    }

    public function setOpt($key, $value) {
        curl_setopt($this->ch, $key, $value);
        return $this;
    }

    public function setMethod($method) {
        $method = strtoupper($method);

        if (!in_array($method, array("GET", "POST", "PUT", "DELETE", "PATCH"))) {
            throw new Exception("Method not supported");
        }

        $this->method = $method;

        return $this;
    }

    public function setParams(array $params = array()) {
        $this->params = $params;

        return $this;
    }

    public function setUrl($url) {
        $this->url =  $url;

        return $this;
    }

    public function execute($url = null) {
        if ($url !== null) {
            $this->setUrl($url);
        }

        if ($this->url === null) {
            throw new CoreException("URL not set");
        }

        $this->setOpt(CURLOPT_CUSTOMREQUEST, $this->method);

        switch ($this->method) {
            case "POST":
            case "PUT":
            case "PATCH":
                $this->setOpt(CURLOPT_POSTFIELDS, http_build_query($this->params));
                break;
            case "GET":
                if (count($this->params)) {
                    $this->url .= "?".http_build_query($this->params);
                }
                break;
        }

        $this->setOpt(CURLOPT_URL, $this->url);

        Log::debug("About to ".$this->method." ".$this->url);

        $rawResponse = curl_exec($this->ch);

        if ($this->getError() !== 0) {
            throw new CurlException(
                $this->getErrorMessage(),
                $this->getError()
            );
        }

        $response = new CurlResponse();

        $response->setInfo(
            $this->getRequestInfo()
        );
        $response->setHeadersFromString(
            $this->getRequestHeaders($rawResponse)
        );
        $response->setBody(
            $this->getRequestBody($rawResponse)
        );

        return $response;
    }

    protected function getRequestInfo() {
        return curl_getinfo($this->ch);
    }

    protected function getErrorMessage() {
        return curl_error($this->ch);
    }

    protected function getError() {
        return curl_errno($this->ch);
    }

    protected function getRequestHeaders($raw) {
        $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        return substr($raw, 0, $headerSize);
    }

    protected function getRequestBody($raw) {
        $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        return substr($raw, $headerSize);
    }
}
