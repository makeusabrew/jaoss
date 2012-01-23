<?php

class JaossResponse {
    
    protected $body = NULL;
    protected $isRedirect = false;
    protected $redirectUrl = "";
    protected $responseCode = 200;
    protected $path = NULL;
    protected $headers = array();
    protected $etag = null;
    protected $ifNoneMatch = null;

    const HTTP_VERSION = "HTTP/1.1";

    public function getBody() {
        return $this->body;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    public function setResponseCode($code) {
        $this->responseCode = $code;
    }

    public function setRedirect($url, $code = 303) {
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
            return false;
        } 

        // check for 304
        if ($this->getEtag() === $this->ifNoneMatch) {
            $this->setResponseCode(304);
        } else {
            $this->addHeader('ETag', $this->getEtag());
        }

        header($this->getHeaderString());
        foreach ($this->getHeaders() as $key => $value) {
            header($key.": ".$value);
        }

        return $this->getResponseCode() !== 304;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getHeader($key) {
        return isset($this->headers[$key]) ? $this->headers[$key]: null;
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
            304 => "Not Modified",
            404 => "Not Found",
            500 => "Internal Server Error",
        );
        $headerString = $headers[$this->getResponseCode()];
        return self::HTTP_VERSION." ".$this->getResponseCode(). " ".$headerString;
    }

    public function addHeader($key, $value) {
        $this->headers[$key] = $value;
    }

    public function send() {
        if ($this->sendHeaders()) {
            echo $this->getBody();
        }
    }

    public function setIfNoneMatch($match) {
        $this->ifNoneMatch = $match;
    }

    public function getEtag() {
        //$headers = implode("|", $this->getHeaders());
        $headers = "";
        $body = $this->getBody();
        return '"'.sha1($headers."_".$body).'"';
    }
}
