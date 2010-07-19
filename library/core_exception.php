<?php
class CoreException extends Exception {
	public function __construct($msg = "", $code = 0, Exception $previous = NULL) {
		parent::__construct($msg, $code, $previous);
		Log::debug("CoreException thrown [".$this->getMessage()."]", "-v");
	}
}
