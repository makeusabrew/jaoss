<?php
class JaossRequest {
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
    private static $instance = NULL;

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new JaossRequest();
        }
        return self::$instance;
    }
	
	public function __construct() {
        $this->sapi = php_sapi_name();
        if ($this->sapi == "cli") {
            // abadon all hope... for now @todo improve
            return;
        }
		$this->folder_base = substr($_SERVER["PHP_SELF"], 0, strpos($_SERVER["PHP_SELF"], "index.php"));
        $this->base_href = "http://".$_SERVER["SERVER_NAME"].$this->folder_base;
		$this->setUrl(substr($_SERVER["REQUEST_URI"], strlen($this->folder_base)-1));
		$queryString = strrpos($this->url, "?");
		if ($queryString !== FALSE) {
			$this->query_string = substr($this->url, $queryString+1);
			$this->setUrl(substr($this->url, 0, $queryString));
		} else {
			$this->query_string = "";
		}
        $this->method = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : NULL;
        $this->ajax = isset($_SERVER["HTTP_X_REQUESTED_WITH"]) ? true : false;
        $this->referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : NULL;
        $this->ip = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : NULL;
        $this->hostname = isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : NULL;
	}

	public function setUrl($url) {
		$this->url = $url;
        $this->full_url = substr($this->getBaseHref(), 0, -1).$url;
	}
	
	public function dispatch($url = null) {
        if ($url !== null) {
            $this->setUrl($url);
        }
		if ($this->url === NULL) {
			throw new CoreException("No URL to dispatch");
		}
		$path = PathManager::matchUrl($this->url);
        
        try {
            $this->response = $path->run($this);
        } catch (CoreException $e) {
            if ($e->getCode() == CoreException::PATH_REJECTED) {
                // right then, mark as discarded and try again...
                $path->setDiscarded(true);
                return $this->dispatch($this->url);
            } else {
                throw $e;
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

    public function getVar($var) {
        return (isset($_REQUEST[$var])) ? $_REQUEST[$var] : null;
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
}
