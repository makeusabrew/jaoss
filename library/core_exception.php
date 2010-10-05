<?php
class CoreException extends Exception {
	const OK = 0;
	const URL_NOT_FOUND = 1;
	const ACTION_NOT_FOUND = 2;
	const TPL_NOT_FOUND = 3;
	const TPL_DIR_NOT_WRITABLE = 4;
    const LOG_FILE_ERROR = 5;
	
	private $args = array();
	
	public function __construct($msg = "", $code = 0, $args = array(), Exception $previous = NULL) {
		parent::__construct($msg, $code, $previous);
		$this->args = $args;
        if ($code != CoreException::LOG_FILE_ERROR) {
            Log::debug("CoreException thrown [".$this->getMessage()."]", "-v");
        }
	}
	
	public function getArg($a) {
		return (isset($this->args[$a])) ? $this->args[$a] : NULL;
	}
	
	public function ga($a) {
		return "<strong>".$this->getArg($a)."</strong>";
	}	
}
