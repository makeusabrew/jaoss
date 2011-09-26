<?php

class ErrorHandler {
	private $smarty;
    private $response;
	public function __construct() {
		require_once(JAOSS_ROOT."library/Smarty/libs/Smarty.class.php");
		
		$this->smarty = new Smarty();		
		$this->smarty->template_dir	= array(JAOSS_ROOT."library/errors");
        $compile_dir = Settings::getValue("smarty.compile_dir", false);
        // we might be running tests stand-alone, in which case just go for the system's tmp dir and hope for the best
        if ($compile_dir === false) {
            $compile_dir = sys_get_temp_dir();
        }
        $this->smarty->compile_dir = $compile_dir;
        
        $this->response = new JaossResponse();
	}
		
	public function handleError($e) {
		$code = $e->getCode();
        $displayErrors = Settings::getValue("errors.verbose", false);
        $app = Settings::getValue("errors.app", false);
        $controller = Settings::getValue("errors.controller", false);
        $action = Settings::getValue("errors.action", false);

        try {
            Log::warn("Handling error of type [".get_class($e)."] with message [".$e->getMessage()."]");
        } catch (CoreException $ex) {
            // if something goes wrong logging the error then we're probably in all sorts of trouble, so
            // just swallow the exception so the original error can be shown.
        }

        /**
         * special edge cases here
         */
        if ($e instanceof CoreException && $e->getCode() == CoreException::TPL_DIR_NOT_WRITABLE) {
            // we assume if we've got this error that the user's chosen dir isn't writable, so
            // we need to switch to one we know (hope!) is to render the error
            $this->smarty->compile_dir = '/tmp';
        }
        if ($e instanceof CoreException) {
            $path = "core/{$code}.tpl";
            $this->response->setResponseCode(404);
        } else if ($e instanceof ErrorException) {
            $path = "core/phperror.tpl";
            $this->response->setResponseCode(500);
        } else if ($e instanceof PDOException) {
            $path = "db/{$code}.tpl";
            $this->response->setResponseCode(500);
        } else if ($e instanceof SmartyCompilerException) {
            $path = "smarty/{$code}.tpl";
            $this->response->setResponseCode(500);
        } else {
            $path = "unknown_exception.tpl";
            $this->response->setResponseCode(500);
        }
        if ($displayErrors) {
            $this->smarty->assign("e", $e);
            if ($this->smarty->templateExists($path)) {
                $this->response->setBody($this->smarty->fetch($path));
            } else {
                $this->smarty->assign("code", $code);
                $this->smarty->assign("path", $path);
                $this->response->setBody($this->smarty->fetch("unknown_code.tpl"));
            }
            return;
        } else if ($app && $controller && $action) {
            $controller = Controller::factory($controller, $app);
            $path = new JaossPath();
            $path->setApp($app);
            $controller->setPath($path);
            $controller->init();
            $controller->$action($e, $this->response->getResponseCode());
            $this->response->setBody(
                str_pad($controller->getResponse()->getBody(), 512)
            );
            return;
        }
        // fallback on static HTML
        $target = PROJECT_ROOT."public/errordocs/".$this->response->getResponseCode().".html";
        if (file_exists($target)) {
            $this->response->setBody(file_get_contents($target));
        } else {
            $this->response->setBody($this->response->getHeaderString());   // better than nothing...
        }
	}

    public function getResponse() {
        return $this->response;
    }
}
