<?php
class JaossRequest {
    protected $response = NULL;
    protected $folder_base = NULL;
    protected $url = NULL;
    protected $query_string = NULL;
    protected $method = NULL;
    protected $base_href = NULL;
    protected $full_url = NULL;
    protected $ajax = false;
    protected $pjax = false;
    protected $referer = NULL;
    protected $sapi = NULL;
    protected $ip = NULL;
    protected $hostname = NULL;
    protected $userAgent = NULL;
    protected $timestamp = NULL;
    protected $headers = array();
    protected $port = null;
    protected $protocol = "http";

    protected $cacheKey = NULL;
    protected $cacheDisabled = false;

    private static $instance = NULL;

    public static function getInstance() {
        if (self::$instance === NULL) {
            if (Settings::getValue("request", "handler", false) == "test" && php_sapi_name() === "cli") {
                self::$instance = new TestRequest();
            } else {
                self::$instance = new JaossRequest();
                self::$instance->setProperties($_SERVER);
            }
        }
        return self::$instance;
    }

    public static function destroyInstance() {
        self::$instance = null;
    }
    
    public function __construct() {
        $this->sapi = php_sapi_name();
    }

    public function setProperties(array $reqData = array()) {
        $basePath = basename($reqData["PHP_SELF"]);  // this is whatever the front controller script is, e.g. index.php

        if (strpos($reqData["PHP_SELF"], "public/".$basePath) !== false) {
            // we're probably running off http://localhost/foo/bar, so adjust base path
            $basePath = "public/".$basePath;
        }

        $this->folder_base = substr($reqData["PHP_SELF"], 0, strpos($reqData["PHP_SELF"], $basePath));

        if (isset($reqData["SERVER_NAME"])) {

            if ((isset($reqData['SSL'])   && $reqData['SSL'] == 'on') ||
                (isset($reqData['HTTPS']) && $reqData['HTTPS'] != 'off')) {

                $this->protocol = 'https';

            } else {
                $this->protocol = 'http';
            }

            $this->port      = $reqData['SERVER_PORT'];

            $this->base_href = $this->protocol."://".$reqData["SERVER_NAME"];

            if (($this->protocol === "https" && $this->port != 443) ||
                ($this->protocol === "http"  && $this->port != 80)) {

                $this->base_href .= ":".$this->port;
            }

            $this->base_href .= $this->folder_base;
        } else {
            $this->base_href = Settings::getValue("site", "base_href", "http://unknown/");
        }


        if (isset($reqData["REQUEST_URI"])) {
            $this->setUrl(
                // we're not interested in %20 instead of spaces, so get rid
                urldecode(
                    substr($reqData["REQUEST_URI"], strlen($this->folder_base)-1)
                )
            );
        }

        if (isset($reqData['QUERY_STRING']) && $reqData['QUERY_STRING'] != '') {
            $this->query_string = $reqData['QUERY_STRING'];
            $this->setUrl(substr(
                $this->url,
                0,
                strpos(
                    $this->url,
                    "?".urldecode($this->query_string)
                )
            ));
        } else {
            $this->query_string = "";
        }

        $this->method    = isset($reqData["REQUEST_METHOD"]) ? $reqData["REQUEST_METHOD"] : NULL;
        $this->pjax      = isset($reqData["HTTP_X_PJAX"]) ? true : false;
        $this->ajax      = isset($reqData["HTTP_X_REQUESTED_WITH"]) ? true : false;
        $this->referer   = isset($reqData["HTTP_REFERER"]) ? $reqData["HTTP_REFERER"] : NULL;
        $this->ip        = isset($reqData["REMOTE_ADDR"]) ? $reqData["REMOTE_ADDR"] : NULL;
        $this->hostname  = isset($reqData["SERVER_NAME"]) ? $reqData["SERVER_NAME"] : NULL;
        $this->userAgent = isset($reqData["HTTP_USER_AGENT"]) ? $reqData["HTTP_USER_AGENT"] : NULL;
        $this->timestamp = isset($reqData["REQUEST_TIME"]) ? $reqData["REQUEST_TIME"] : NULL;

        if ($this->sapi !== "cli") {
            $this->headers = apache_request_headers();
        } else if (isset($reqData["_headers"])) {
            $this->headers = $reqData["_headers"];
        }
    }

    public function setUrl($url) {
        $this->url = $url;
        $this->full_url = substr($this->getBaseHref(), 0, -1).$url;
    }

    public function isCacheable() {
        return $this->isGet();
    }
    
    public function dispatch($url = null) {
        if ($url !== null) {
            $this->setUrl($url);
        }
        if ($this->url === NULL) {
            throw new CoreException("No URL to dispatch");
        }

        $path = PathManager::matchUrl($this->url);

        $this->response = new JaossResponse();

        if ($path->isCacheable() &&
            $this->isCacheable() &&
            Cache::isEnabled()) {

            $cache = Cache::getInstance();

            $cacheUrl = $this->url;
            if ($this->query_string != '') {
                $cacheUrl .= "?".$this->query_string;
            }

            Log::info("Attempting to retrieve response contents for [".$cacheUrl."] from cache...");
            $this->cacheKey = Settings::getValue("site", "namespace").sha1($cacheUrl);

            $responseData = $cache->fetch($this->cacheKey);
            if ($cache->fetchHit()) {
                Log::info("cache hit - found response");
                $this->response->setFromArray($responseData);
                return $this->response;
            }
            Log::info("cache miss - no response found");
        } else {
            StatsD::increment("cache.nocache");
        }

        try {
            $this->response = $path->run($this);
        } catch (CoreException $e) {
            if ($e->getCode() == CoreException::PATH_REJECTED) {
                // right then, mark as discarded and try again...
                Log::debug("Reject [".$path->getController()."Controller->".$path->getAction()."] - [".$e->getMessage()."]");

                $path->setDiscarded(true);
                if ($this->cacheKey !== null) {
                    Log::info("Path discarded - discarding cache key");
                    $this->cacheKey = null;
                }
                return $this->dispatch($this->url);
            } else {
                throw $e;
            }
        }
        if ($this->cacheKey          !== null &&
            $this->isCacheDisabled() === false) { // make sure something hasn't explicitly disabled cache during the request

            $cache = Cache::getInstance();

            Log::info("Caching response for URL [".$cacheUrl."] with ttl [".$path->getCacheTtl()."]");
            $cached = $cache->store($this->cacheKey, $this->response->toArray(), $path->getCacheTtl());
            if ($cached) {
                Log::info("Response cached successfully");
            } else {
                Log::warn("Response for URL [".$cacheUrl."] could not be cached!");
            }
        }

        Log::debug("Response Code: ".$this->response->getResponseCode());
        return $this->response;
    }

    public function getResponse() {
        return $this->response;
    }

    public function getMethod() {
        return $this->method;
    }

    public function getBaseHref() {
        return $this->base_href;
    }

    public function getFullUrl() {
        return $this->full_url;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getReferer(){
        return $this->referer;
    }

    public function getQueryString() {
        return $this->query_string;
    }

    public function isGet() {
        return $this->method == "GET";
    }

    public function isPost() {
        return $this->method == "POST";
    }

    public function getVar($var, $default = null) {
        return (isset($_REQUEST[$var])) ? $_REQUEST[$var] : $default;
    }

    public function getPost() {
        return $_POST;
    }
    
    public function getFile($file) {
        return isset($_FILES[$file]) ? $_FILES[$file] : null;
    }

    public function processFile($file) {
        $file = $this->getFile($file);
        return new File($file);
    }
    
    public function isAjax() {
        return $this->ajax;
    }

    public function isPjax() {
        return $this->pjax;
    }
    
    public function disableAjax() {
        $this->ajax = false;
    }
    
    public function getIp() {
       return $this->ip;
    }

    public function getHostname() {
        return $this->hostname;
    }

    public function getFolderBase() {
        return $this->folder_base;
    }

    public function getUserAgent() {
        return $this->userAgent;
    }

    public function getTimestamp() {
        return $this->timestamp;
    }

    public function getSapi() {
        return $this->sapi;
    }

    public function isCacheDisabled() {
        return $this->cacheDisabled;
    }

    public function disableCache() {
        $this->cacheDisabled = true;
    }

    public function getHeader($key) {
        return isset($this->headers[$key]) ? $this->headers[$key] : null;
    }
}
