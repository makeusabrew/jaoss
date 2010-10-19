<?php
class Request {
	private $folder_base = NULL;
	private $url = NULL;
	private $query_string = NULL;
    private $method = NULL;
    private $base_href = NULL;
    private $ajax = false;
	private $referer = NULL;
	
	public function __construct() {
        if (php_sapi_name() == "cli") {
            // abadon all hope... for now @todo improve
            return;
        }
		$this->folder_base = substr($_SERVER["PHP_SELF"], 0, strpos($_SERVER["PHP_SELF"], "index.php"));
        $this->base_href = "http://".$_SERVER["SERVER_NAME"].$this->folder_base;
		$this->url = substr($_SERVER["REQUEST_URI"], strlen($this->folder_base)-1);
		$queryString = strrpos($this->url, "?");
		if ($queryString !== FALSE) {
			$this->query_string = substr($this->url, $queryString+1);
			$this->url = substr($this->url, 0, $queryString);
		} else {
			$this->query_string = "";
		}
        $this->method = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : NULL;
        $this->ajax = isset($_SERVER["HTTP_X_REQUESTED_WITH"]) ? true : false;
        $this->referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : NULL;
	}
	
	public function overrideUrl($url) {
		$this->url = $url;
	}
	
	public function dispatch() {
		if ($this->url === NULL) {
			throw new CoreException("No URL to dispatch");
		}
		$path = PathManager::matchUrl($this->url);
		return $path->run($this);
	}

    public function getMethod() {
        return $this->method;
    }

    public function getBaseHref() {
        return $this->base_href;
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

    public function doRedirect($url, $code) {
        header("Location: {$url}", true, $code);
        return true;
    }
}
