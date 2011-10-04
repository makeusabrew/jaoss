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
    protected $referer = NULL;
    protected $sapi = NULL;
    protected $ip = NULL;
    protected $hostname = NULL;
    protected $userAgent = NULL;
    protected $timestamp = NULL;

    protected $cacheKey = NULL;
    protected $cacheDisabled = false;

    private static $instance = NULL;

    public static function getInstance() {
        if (self::$instance === NULL) {
            if (Settings::getValue("request", "handler", false) == "test" && php_sapi_name() === "cli") {
                self::$instance = new TestRequest();
            } else {
                self::$instance = new JaossRequest($_SERVER);
            }
        }
        return self::$instance;
    }

    public static function destroyInstance() {
        self::$instance = null;
    }
	
	public function __construct(array $reqData = array()) {
        $this->sapi = php_sapi_name();
        $basePath = basename($reqData["PHP_SELF"]);  // should be index.php or xhprof.php
        // we now support subfolders, conditionally anyway
        if (substr_compare($reqData["PHP_SELF"], "public/".$basePath, -strlen("public/".$basePath), strlen("public/".$basePath)) === 0) {
            // we're probably running off http://localhost/foo/bar, so adjust base path
            $basePath = "public/".$basePath;
        }
		$this->folder_base = substr($reqData["PHP_SELF"], 0, strpos($reqData["PHP_SELF"], $basePath));
        if (isset($reqData["SERVER_NAME"])) {
            $this->base_href = "http://".$reqData["SERVER_NAME"].$this->folder_base;
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
		$queryString = strrpos($this->url, "?");
		if ($queryString !== FALSE) {
			$this->query_string = substr($this->url, $queryString+1);
			$this->setUrl(substr($this->url, 0, $queryString));
		} else {
			$this->query_string = "";
		}
        $this->method    = isset($reqData["REQUEST_METHOD"]) ? $reqData["REQUEST_METHOD"] : NULL;
        $this->ajax      = isset($reqData["HTTP_X_REQUESTED_WITH"]) ? true : false;
        $this->referer   = isset($reqData["HTTP_REFERER"]) ? $reqData["HTTP_REFERER"] : NULL;
        $this->ip        = isset($reqData["REMOTE_ADDR"]) ? $reqData["REMOTE_ADDR"] : NULL;
        $this->hostname  = isset($reqData["SERVER_NAME"]) ? $reqData["SERVER_NAME"] : NULL;
        $this->userAgent = isset($reqData["HTTP_USER_AGENT"]) ? $reqData["HTTP_USER_AGENT"] : NULL;
        $this->timestamp = isset($reqData["REQUEST_TIME"]) ? $reqData["REQUEST_TIME"] : NULL;
	}

	public function setUrl($url) {
		$this->url = $url;
        $this->full_url = substr($this->getBaseHref(), 0, -1).$url;
	}

    public function isCacheable() {
        return ($this->isGet() && $this->query_string == "");
    }
	
	public function dispatch($url = null) {
        if ($url !== null) {
            $this->setUrl($url);
        }
		if ($this->url === NULL) {
			throw new CoreException("No URL to dispatch");
		}

		$path = PathManager::matchUrl($this->url);

        if ($path->isCacheable() &&
            $this->isCacheable() &&
            Settings::getValue("site", "cache_enabled", false) == true) {

            Log::info("Attempting to retrieve URL contents [".$this->url."] from cache...");
            $this->cacheKey = Settings::getValue("site", "namespace").sha1($this->url);
            $success = false;
            $response = Cache::fetch($this->cacheKey, $success);
            if ($success === true) {
                Log::info("cache hit");
                $this->response = $response;
                return $this->response;
            }
            Log::info("cache miss");
        }

        try {
            $this->response = $path->run($this);
        } catch (CoreException $e) {
            if ($e->getCode() == CoreException::PATH_REJECTED) {
                // right then, mark as discarded and try again...
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

            Log::info("Caching response for URL [".$this->url."] with ttl [".$path->getCacheTtl()."]");
            $cached = Cache::store($this->cacheKey, $this->response, $path->getCacheTtl());
            if ($cached) {
                Log::info("Cache stored successfully");
            } else {
                Log::warn("URL [".$this->url."] could not be cached!");
            }
        }
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
    
    // euch, clumsy
    public function getGet() {
    	return $_GET;
    }

    public function getFiles() {
        return $_FILES;
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
}
