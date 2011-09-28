<?php
class App {
	private $folder = NULL;
	private $loaded = false;
	private $paths_loaded = false;
    private $paths_file = null;
	
	private $has_controllers = false;
	private $has_models = false;
	private $has_views = false;
	
	public function __construct($folder = NULL) {
		if ($folder !== NULL) {
			$this->setFolder($folder);
            $this->setPathsFile(PROJECT_ROOT."apps/".$this->getFolder()."/paths.php");
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

    public function setPathsFile($file) {
        $this->paths_file = $file;
    }
	
	public function loadPaths() {
		if ($this->paths_loaded) {
			return false;
		}
		
        $path = $this->paths_file;
        Log::verbose("Looking for [".$path."]");
		if (file_exists($path)) {
			include($path);
			$this->setLoaded(true);
			return true;
		}
        Log::verbose("No paths.php found for app [".$this->getTitle()."]");
		
		return false;		
	}
}
