<?php
class CoreException extends Exception {
	public function __construct() {
		Log::debug($this->getMsg(), "-v");
		parent::__construct();
	}
}
