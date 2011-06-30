<?php
class CoreException extends Exception {
	const OK = 0;
	const URL_NOT_FOUND = 1;
	const ACTION_NOT_FOUND = 2;
	const TPL_NOT_FOUND = 3;
	const TPL_DIR_NOT_WRITABLE = 4;
    const LOG_FILE_ERROR = 5;
    const INVALID_MODE = 6;
    const CONTROLLER_CLASS_NOT_FOUND = 7;
    const PATH_REJECTED = 8;
    const VARIABLE_ALREADY_ASSIGNED = 9;
    const NO_PATHS_LOADED = 10;
    const NO_PATH_FOUND_FOR_OPTIONS = 11;
	
	protected $args = array();
	
	public function __construct($msg = "", $code = 0, $args = array()) {
		parent::__construct($msg, $code);
		$this->args = $args;
        if ($code !== CoreException::LOG_FILE_ERROR) {
            Log::verbose("CoreException thrown [".$this->getMessage()."]");
        }
	}
	
	public function getArg($a) {
		return isset($this->args[$a]) ? $this->args[$a] : null;
	}
	
	public function ga($a) {
		return "<strong>".$this->getArg($a)."</strong>";
	}	
}
