<?php
class App {
	private $folder = NULL;
	private $loaded = FALSE;
	private $paths_loaded = FALSE;
	
	private $has_controllers = FALSE;
	private $has_models = FALSE;
	private $has_views = FALSE;
	
	public function __construct($folder = NULL) {
		if ($folder !== NULL) {
			$this->setFolder($folder);
		}
	}
	
	public function getTitle() {
		return ucfirst($this->folder);
	}
	
	public function setLoaded($loaded) {
		$this->loaded = $loaded;
	}
	
	public function setFolder($folder) {
		$this->folder = $folder;
	}
	
	public function getLoaded() {
		return $this->loaded;
	}
	
	public function getFolder() {
		return $this->folder;
	}
	
	public function loadPaths() {
		if ($this->paths_loaded) {
			return FALSE;
		}
		
        $path = "apps/".$this->folder."/paths.php";
        Log::debug("Looking for [".$path."]");
		if (file_exists($path)) {
			include($path);
			$this->setLoaded(TRUE);
			return TRUE;
		}
		
		return FALSE;		
	}
}
