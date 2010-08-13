<?php
class CoreException extends Exception {
	const OK = 0;
	const URL_NOT_FOUND = 1;
	
	private $args = array();
	
	public function __construct($msg = "", $code = 0, $args = array(), Exception $previous = NULL) {
		parent::__construct($msg, $code, $previous);
		$this->args = $args;
		Log::debug("CoreException thrown [".$this->getMessage()."]", "-v");
	}
	
	public function getArg($a) {
		return (isset($this->args[$a])) ? $this->args[$a] : NULL;
	}	
}
