<?php

class JaossResponse {
    
    protected $body = NULL;
    protected $isRedirect = false;
    protected $redirectUrl = "";
    protected $responseCode = 200;
    protected $path = NULL;
    protected $headers = array();

    const HTTP_VERSION = "HTTP/1.0";

    public function getBody() {
        return $this->body;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    public function setResponseCode($code) {
        $this->responseCode = $code;
    }

    public function setRedirect($url, $code = 302) {
        $this->isRedirect = true;
        $this->redirectUrl = $url;
        $this->setResponseCode($code);
    }

    public function isRedirect() {
        return $this->isRedirect;
    }

    public function getResponseCode() {
        return $this->responseCode;
    }

    public function sendHeaders() {
        if ($this->isRedirect()) {
            header("Location: ".$this->redirectUrl, true, $this->getResponseCode());
            exit;
        } 
        header($this->getHeaderString());
        foreach ($this->headers as $key => $value) {
            header($key.": ".$value);
        }
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function getPath() {
        return $this->path;
    }
    
    public function getRedirectUrl() {
        return $this->redirectUrl;
    }

    public function getHeaderString() {
        $headers = array(
            200 => "OK",
            404 => "Not Found",
            500 => "Internal Server Error",
        );
        $headerString = $headers[$this->getResponseCode()];
        return self::HTTP_VERSION." ".$this->getResponseCode(). " ".$headerString;
    }

    public function addHeader($key, $value) {
        $this->headers[$key] = $value;
    }
}
