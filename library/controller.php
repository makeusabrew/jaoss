<?php
abstract class Controller {
	protected $smarty = NULL;
	protected $path = NULL;
	protected $adminUser = NULL;
	protected $session = NULL;
    protected $request = NULL;
    
    protected $var_stack = array();

    public function init() {
		return TRUE;
    }

	public function __construct($request = NULL) {
		require_once("library/Smarty-3.0rc1/libs/Smarty.class.php");
		
		$this->smarty = new Smarty();
		
		$apps = AppManager::getAppPaths();
		$tpl_dirs = array("apps/");
		foreach ($apps as $app) {
			$tpl_dirs[] = "apps/{$app}/views/";
		}
		
		$this->smarty->template_dir	= $tpl_dirs;
		$this->smarty->compile_dir = Settings::getValue("smarty", "compile_dir");

        $this->request = $request;
		
		if (!$this->request->isAjax()) {
			$this->assign("base_href", $this->request->getBaseHref());
	        $this->assign("current_url", $this->request->getUrl());
	        $this->assign("messages", FlashMessenger::getMessages());
	    }

        $this->session = Session::getInstance();
	}
	
	public function setPath($path) {
		$this->path = $path;
	}
	
	public static function factory($controller, $app_path = NULL) {
		$c_class = $controller."Controller";
		if (class_exists($c_class)) {
			return new $c_class;
		}
		// can force a path if required
		if ($app_path !== NULL) {
			$path = "apps/{$app_path}/controllers/".strtolower($controller).".php";
			if (file_exists($path)) {
				include($path);
				return self::factory($controller);
			}
		}
		$apps = AppManager::getAppPaths();
		foreach ($apps as $app) {
			$path = "apps/{$app}/controllers/".strtolower($controller).".php";
			if (file_exists($path)) {
				include($path);
				return self::factory($controller);
			}
		}
		throw new CoreException("Could not find controller in any path: {$controller}");
	}
	
	public function getMatch($match, $default=NULL) {
		if (!isset($this->path->matches[$match])) {
			return $default;
		}
		return $this->path->matches[$match];
	}

    public function redirect($url, $message = NULL) {
    	// override for ajax requests, but add the URL to the response
    	if ($this->request->isAjax()) {
    		$this->assign("redirect", $url);
    		return $this->render(null);
    	}
    	if ($message) {
    		FlashMessenger::addMessage($message);
    	}
        header("Location: {$url}", TRUE, 303);
    }
	
	public function render($template) {
		if ($this->request->isAjax()) {
			return $this->renderJson();
		}
		// normal request
		foreach ($this->var_stack as $var => $val) {
			$this->smarty->assign($var, $val);
		}
		if ($this->smarty->templateExists($template.".tpl")) {
			return $this->smarty->fetch($template.".tpl");
		} else {
			throw new CoreException(
				"Template Not Found",
				CoreException::TPL_NOT_FOUND,
				array(
					"paths" => $this->smarty->template_dir,
					"tpl" => $template,
				)
			);
		}
	}
	
	public function renderJson() {
		if (!isset($this->var_stack["msg"])) {
			$this->var_stack["msg"] = "OK";
		}
		foreach ($this->var_stack as $var => $val) {
			$data[$var] = $val;
		}
		return json_encode($data);
	}
	
	public function renderStatic($template) {
		if ($this->smarty->templateExists("static/".$template.".tpl")) {
			return $this->fetch("static/".$template.".tpl");
		}
		// manual for HTML files
		foreach ($this->smarty->template_dir as $dir) {
			if (file_exists($dir."static/".$template.".html")) {
				return file_get_contents($dir."static/".$template.".html");
			}
		}
		throw new CoreException("no static template found");
	}
	
	public function assign($var, $value) {
		$this->var_stack[$var] = $value;
	}
    
    public function setFlash($flash) {
        $this->session->setFlash($flash);
    }

    public function getFlash($flash) {
       return $this->session->getFlash($flash);
    }
}
