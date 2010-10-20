<?php
abstract class Controller {
	protected $smarty = NULL;
	protected $path = NULL;
	protected $adminUser = NULL;
	protected $session = NULL;
    protected $request = NULL;
    protected $response = NULL;
    
    protected $var_stack = array();

    public function init() {
		return TRUE;
    }

	public function __construct($request = NULL) {
		require_once("library/Smarty-3.0rc4/libs/Smarty.class.php");
		
		$this->smarty = new Smarty();
		
		$apps = AppManager::getAppPaths();
		$tpl_dirs = array(PROJECT_ROOT."apps/");
		foreach ($apps as $app) {
			$tpl_dirs[] = PROJECT_ROOT."apps/{$app}/views/";
		}
		
		$this->smarty->template_dir	= $tpl_dirs;
		$this->smarty->compile_dir = Settings::getValue("smarty", "compile_dir");

        $this->request = $request;
        $this->response = new JaossResponse();
		
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
			$path = PROJECT_ROOT."apps/{$app_path}/controllers/".strtolower($controller).".php";
			if (file_exists($path)) {
				include($path);
				return self::factory($controller);
			}
		}
		$apps = AppManager::getAppPaths();
		foreach ($apps as $app) {
			$path = PROJECT_ROOT."apps/{$app}/controllers/".strtolower($controller).".php";
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
        // always add the flash message - if the ajax handler obeys the 
        // redirect we will pick it up next render
    	if ($message) {
    		FlashMessenger::addMessage($message);
    	}
    	if ($this->request->isAjax()) {
    		$this->assign("redirect", $url);
            $this->response->setBody($this->renderJson());
            return true;
    	} else {
            $this->response->setRedirect($url, 303);
            return true;
        }
    }
	
	public function render($template) {
		if ($this->request->isAjax()) {
			$this->response->setBody($this->renderJson());
            return true;
		} else {
            $this->response->setBody($this->renderTemplate($template));
            return true;
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

    public function renderTemplate($template) {
		if ($this->smarty->templateExists($template.".tpl")) {
            $this->assign("base_href", $this->request->getBaseHref());
            $this->assign("current_url", $this->request->getUrl());
            $this->assign("messages", FlashMessenger::getMessages());

            foreach ($this->var_stack as $var => $val) {
                $this->smarty->assign($var, $val);
            }
			return $this->smarty->fetch($template.".tpl");
		}

        throw new CoreException(
            "Template Not Found",
            CoreException::TPL_NOT_FOUND,
            array(
                "paths" => $this->smarty->template_dir,
                "tpl" => $template,
            )
        );
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

    public function unassign($var) {
        unset($this->var_stack[$var]);
    }
    
    public function setFlash($flash) {
        $this->session->setFlash($flash);
    }

    public function getFlash($flash) {
       return $this->session->getFlash($flash);
    }
    
    public function getResponse() {
        $this->response->setPath($this->path);
        return $this->response;
    }
    
    public function setResponseCode($code) {
        $this->response->setResponseCode($code);
    }
}
