<?php

class JaossResponse {
    
    protected $body         = null;
    protected $isRedirect   = false;
    protected $redirectUrl  = null;
    protected $responseCode = 200;
    protected $path         = null;
    protected $headers      = array();
    protected $ifNoneMatch  = null;

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
        $headers = implode("|", $this->getHeaders());
        $body = $this->getBody();
        return '"'.sha1($headers."_".$body).'"';
    }

    public function isInitialised() {
        return $this->body !== null || count($this->headers) || $this->redirectUrl !== null;
    }

    public function toArray() {
        $pathData = null;
        if ($this->path instanceof JaossPath) {
            $pathData = $this->path->toArray();
        }
        return array(
            "body"         => $this->body,
            "isRedirect"   => $this->isRedirect,
            "redirectUrl"  => $this->redirectUrl,
            "responseCode" => $this->responseCode,
            "path"         => $pathData,
            "headers"      => $this->headers,
            "ifNoneMatch"  => $this->ifNoneMatch,
        );
    }

    public function setFromArray(array $data = array()) {
        if (isset($data['body'])) {
            $this->body = $data['body'];
        }
        if (isset($data['isRedirect'])) {
            $this->isRedirect = $data['isRedirect'];
        }
        if (isset($data['redirectUrl'])) {
            $this->redirectUrl = $data['redirectUrl'];
        }
        if (isset($data['responseCode'])) {
            $this->responseCode = $data['responseCode'];
        }
        if (isset($data['path']) ) {
            if (is_array($data['path'])) {
                $this->path = new JaossPath();
                $this->path->setFromArray($data['path']);
            }
        }
        if (isset($data['headers'])) {
            $this->headers = $data['headers'];
        }
        if (isset($data['ifNoneMatch'])) {
            $this->ifNoneMatch = $data['ifNoneMatch'];
        }
    }
}
