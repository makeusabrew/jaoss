<?php

class ErrorHandler {
	private $smarty;
	public function __construct() {
		require_once("library/Smarty-3.0rc4/libs/Smarty.class.php");
		
		$this->smarty = new Smarty();		
		$this->smarty->template_dir	= array(PROJECT_ROOT."library/errors");
		//$this->smarty->compile_dir = Settings::getValue("smarty", "compile_dir");
        $this->smarty->compile_dir = "/tmp";
	}
		
	public function handleError($e) {
		$code = $e->getCode();
        header($e->getHeaderString());
		$this->smarty->assign("e", $e);
		return $this->smarty->fetch("core/{$code}.tpl");
	}
}
