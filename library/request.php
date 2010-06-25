<?php
class Request {
	protected $folder_base = NULL;
	protected $url = NULL;
    protected $method = NULL;
	
	public function __construct() {
		$this->folder_base = substr($_SERVER["PHP_SELF"], 0, strpos($_SERVER["PHP_SELF"], "index.php"));
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

    public function
}
