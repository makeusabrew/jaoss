<?php

class ErrorHandler {
	private $smarty;
	public function __construct() {
		require_once("library/Smarty-3.0rc4/libs/Smarty.class.php");
		
		$this->smarty = new Smarty();		
		$this->smarty->template_dir	= array(JAOSS_ROOT."library/errors");
		//$this->smarty->compile_dir = Settings::getValue("smarty", "compile_dir");
        $this->smarty->compile_dir = "/tmp";
	}
		
	public function handleError($e) {
		$code = $e->getCode();
        header($e->getHeaderString());
		$this->smarty->assign("e", $e);
        $displayErrors = Settings::getValue("errors.verbose", false);
        $app = Settings::getValue("errors.app", false);
        $controller = Settings::getValue("errors.controller", false);
        $action = Settings::getValue("errors.action", false);
        if ($displayErrors) {
            return $this->smarty->fetch("core/{$code}.tpl");
        } else if ($app && $controller && $action) {
            $controller = Controller::factory($controller, $app);
            $controller->setPath(new Path());
            $controller->init();
            $controller->$action($e);
            $body = $controller->getResponse()->getBody();
            return str_pad($body, 512);
        }
        // fallback on static HTML
        $target = PROJECT_ROOT."public/errordocs/".$e->getResponseCode().".html";
        if (file_exists($target)) {
            return file_get_contents($target);
        } else {
            return $e->getHeaderString();   // better than nothing...
        }
	}
}
