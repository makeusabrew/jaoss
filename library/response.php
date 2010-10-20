<?php

class JaossResponse {
    
    protected $body = NULL;
    protected $isRedirect = false;
    protected $redirectUrl = "";
    protected $responseCode = 200;
    protected $path = NULL;

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

    public function echoHeaders() {
        if ($this->isRedirect()) {
            header("Location: ".$this->redirectUrl, true, $this->getResponseCode());
            exit;
        } 
        $headers = array(
            200 => "OK",
            404 => "Not Found",
            500 => "Internal Server Error",
        );
        $headerString = $headers[$this->getResponseCode()];
        header("HTTP/1.0 ".$this->getResponseCode()." ".$headerString);
    }

    public function echoBody() {
        echo $this->getBody();
    }

    public function setPath($path) {
        $this->path = $path;
    }
}
