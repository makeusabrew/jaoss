<?php
class Request {
	private $folder_base = NULL;
	private $url = NULL;
    private $method = NULL;
    private $base_href = NULL;
	
	public function __construct() {
		$this->folder_base = substr($_SERVER["PHP_SELF"], 0, strpos($_SERVER["PHP_SELF"], "index.php"));
        $this->base_href = "http://".$_SERVER["SERVER_NAME"].$this->folder_base;
		$this->url = substr($_SERVER["REQUEST_URI"], strlen($this->folder_base)-1);
        $this->method = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : NULL;
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

    public function isGet() {
        return $this->method == "GET";
    }

    public function isPost() {
        return $this->method == "POST";
    }

    public function getVar($var) {
        if ($this->isPost()) {
            return $_POST[$var];
        }
        return $_GET[$var];
    }
}
