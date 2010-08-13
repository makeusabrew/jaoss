<?php

class ErrorHandler {
	private $smarty;
	public function __construct() {
		require_once("library/Smarty-3.0rc1/libs/Smarty.class.php");
		
		$this->smarty = new Smarty();		
		$this->smarty->template_dir	= array("library/errors");
		$this->smarty->compile_dir = Settings::getValue("smarty", "compile_dir");
	}
		
	public function handleError($e) {
		$code = $e->getCode();
		$this->smarty->assign("e", $e);
		return $this->smarty->fetch("core/{$code}.tpl");
	}
}
