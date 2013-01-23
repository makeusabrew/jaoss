<?php

class CurlResponse {
    protected $info = array();
    protected $headers;
    protected $body;
    
    public function setInfo(array $info = array()) {
        $this->info = $info;
    }

    public function setHeadersFromString($headerString) {
        $this->setHeaders(
            $this->parseHeaderString($headerString)
        );
    }

    public function setHeaders($headers) {
        $this->headers = $headers;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getHeader($key) {
        return isset($this->headers[$key]) ? $this->headers[$key] : null;
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

    protected function parseHeaderString($header) {

        $headers = explode("\r\n", $header);
        $final = array();

        foreach ($headers as $header) {
            if (trim($header) === "") {
                continue;
            }

            if (substr($header, 0, 5) === "HTTP/") {
                $final['status'] = $header;
                continue;
            }

            if (preg_match("/^(.+?):(.+)$/", $header, $matches)) {
                $key   = trim($matches[1]);
                $value = trim($matches[2]);

                $final[$key] = $value;
            }
        }

        return $final;
    }
}
