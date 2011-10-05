<?php
abstract class Controller {
    protected $smarty = NULL;
    protected $path = NULL;
    protected $adminUser = NULL;
    protected $session = NULL;
    protected $request = NULL;
    protected $response = NULL;
    protected $errors = array();
    
    protected $var_stack = array();

    public function init() {
    }

    public function __construct($request = NULL) {
        $this->smarty = new Smarty();

        $tpl_dirs = array(PROJECT_ROOT."apps/");
		
        $this->smarty->setTemplateDir($tpl_dirs)
                     ->setCompileDir(Settings::getValue("smarty", "compile_dir"))
                     ->setPluginsDir(array(
            JAOSS_ROOT."library/Smarty/libs/plugins",  // default smarty dir
            JAOSS_ROOT."library/Smarty/custom_plugins",
        ));

        // no setter for this, strangely...
        $this->smarty->compile_check = Settings::getBool("smarty", "compile_check");

        $this->request = $request;
        $this->response = new JaossResponse();
		
        $this->session = Session::getInstance();
    }
	
    public function setPath($path) {
        $this->path = $path;
        if (isset($this->smarty)) {
            $this->smarty->setTemplateDir(array_merge(
                array(PROJECT_ROOT."apps/".$this->path->getApp()."/views/"),
                $this->smarty->getTemplateDir()
            ));
        }
    }
	
    public static function factory($controller, $app_path = NULL, $request = NULL) {
        if (!is_string($controller) || $controller == "") {
            throw new CoreException(
                "Controller::factory must be passed a non-empty string",
                CoreException::EMPTY_CONTROLLER_FACTORY_STRING
            );
        }
        $c_class = $controller."Controller";
        if (!class_exists($c_class)) {
            // can force a path if required
            if ($app_path !== NULL) {
                $path = PROJECT_ROOT."apps/{$app_path}/controllers/".strtolower($controller).".php";
                if (file_exists($path)) {
                    include($path);
                }
            } else {
                $apps = AppManager::getAppPaths();
                foreach ($apps as $app) {
                    $path = PROJECT_ROOT."apps/{$app}/controllers/".strtolower($controller).".php";
                    if (file_exists($path)) {
                        include($path);
                        break;
                    }
                }
            }
        }

        if (class_exists($c_class)) {
            $request = $request ? $request : JaossRequest::getInstance();
            return new $c_class($request);
        }
        throw new CoreException(
            "Could not find controller class '".$c_class."'",
            CoreException::CONTROLLER_CLASS_NOT_FOUND,
            array(
                "controller" => $controller,
                "class" => $c_class,
                "path" => isset($app_path) ? $path : null,
                "apps" => isset($apps) ? $apps : null,
            )
        );
    }
	
    public function getMatch($match, $default=NULL) {
        if (!$this->path->hasMatch($match)) {
            return $default;
        }
        return $this->path->getMatch($match);
    }

    public function redirect($url, $message = NULL) {
        if (is_array($url)) {
            if (!isset($url["controller"])) {
                $url["controller"] = $this->path->getController();
            }
            if (!isset($url["app"])) {
                $url["app"] = $this->path->getApp();
            }
            $url = PathManager::getUrlForOptions($url);
        }
        // always add the flash message - if the ajax handler obeys the 
        // redirect we will pick it up next render
    	if ($message) {
    		FlashMessenger::addMessage($message);
    	}
    	if ($this->request->isAjax()) {
    		$this->assign("redirect", $url);
            return $this->renderJson();
    	} else {
            if ($this->request->getFolderBase() !== '/') {
                $url = substr($this->request->getBaseHref(), 0, -1).$url;
            }
            $this->response->setRedirect($url, 303);
            return true;
        }
    }

    public function redirectAction($action, $message = NULL) {
        return $this->redirect(array("action" => $action), $message);
    }

    public function redirectReferer($message = NULL) {
        $url = $this->request->getReferer();
        if ($url === null) {
            Log::debug("No referer URL found, redirecting to [/]");
            $url = "/";
        }
        return $this->redirect($url, $message);
    }
	
    public function render($template) {
        if (count($this->errors)) {
            $this->assign("_errors", $this->errors);
        }
        if ($this->request->isAjax()) {
            return $this->renderJson();
        } else {
            return $this->renderTemplate($template);
        }
    }
	
    /**
     * render functions always render to the response body
     */
    public function renderJson($extra = array()) {
        $this->response->addHeader('Content-Type', 'application/json');
        $this->response->setBody($this->fetchJson($extra));
        return true;
    }

    public function renderTemplate($template) {
        $this->response->setBody(
            $this->fetchTemplate($template)
        );
        return true;
    }

    /**
     * fetch functions just return the data - this allows you to fetch any
     * template or JSON data without appending it to the response body
     */
    public function fetchJson($extra = array()) {
        foreach ($extra as $var => $val) {
            $this->assign($var, $val);
        }

        $this->assignIfNotSet("msg", "OK");

        foreach ($this->var_stack as $var => $val) {
            // explicitly catch two very common use cases
            // 1. when we've assigned a single instance of an object
            // 2. when we've assigned an array of objects
            //
            // @todo change to instance of SomeInterface instead?
            if ($val instanceof Object) {
                $data[$var] = $val->toArray();
            } else if (is_array($val)) {
                $arrayData = array();
                foreach ($val as $k => $v) {
                    if ($v instanceof Object) {
                        $arrayData[$k] = $v->toArray();
                    } else {
                        $arrayData[$k] = $v;
                    }
                }
                $data[$var] = $arrayData;
            } else {
                $data[$var] = $val;
            }
        }
        return json_encode($data);
    }

    public function fetchTemplate($template) {
        if (!$this->smarty->templateExists($template.".tpl")) {
            throw new CoreException(
                "Template Not Found",
                CoreException::TPL_NOT_FOUND,
                array(
                    "paths" => $this->smarty->template_dir,
                    "tpl" => $template,
                )
            );
        }

        // we have to delay assigning these template vars as we only want them
        // *if* we're rendering a template - but this means we get issues calling
        // render twice. NB we can't just blindly overwrite the vars as things
        // like the flash messages only exist once!
        $this->assignIfNotSet("base_href", $this->request->getBaseHref());
        $this->assignIfNotSet("current_url", $this->request->getUrl());
        $this->assignIfNotSet("full_url", $this->request->getFullUrl());
        $this->assignIfNotSet("messages", FlashMessenger::getMessages());

        foreach ($this->var_stack as $var => $val) {
            $this->smarty->assign($var, $val);
        }

        try {
            return $this->smarty->fetch($template.".tpl");
        } catch (Exception $e) {
            /*
            Smarty::fetch() internally turns on output buffering and then starts
            echoing contents. Therefore because we have exception throwing turned on
            it never gets a chance to call ob_get_clean(), so the exception gets
            flushed along with all the output thus far (not what we want).
            so, flush the buffer manually and throw the exception
            */
            $buffer = ob_get_contents();
            if ($buffer !== false && strlen($buffer) > 0) {
                ob_end_clean();
            }

            throw $e;
        }

    }
	
    public function renderStatic($template) {
        if ($this->smarty->templateExists("static/".$template.".tpl")) {
            return $this->render("static/".$template);
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
        if ($this->isAssigned($var)) {
            throw new CoreException(
                "Variable already assigned",
                CoreException::VARIABLE_ALREADY_ASSIGNED,
                array(
                    "var" => $var,
                    "oldValue" => $this->var_stack[$var],
                    "newValue" => $value,
                )
            );
        }
		$this->var_stack[$var] = $value;
    }

    public function isAssigned($var) {
        return isset($this->var_stack[$var]);
    }

    public function assignIfNotSet($var, $val) {
        if (!$this->isAssigned($var)) {
            $this->assign($var, $val);
        }
    }

    public function unassign($var) {
        unset($this->var_stack[$var]);
    }

    public function unassignAll() {
        $this->var_stack = array();
    }
    
    public function setFlash($flash, $value = true) {
        $this->session->setFlash($flash, $value);
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
    
    public function templateForPattern() {
        $pattern = $this->path->getPattern();
        if (!preg_match("@(?P<tpl>\w+)@", $pattern, $matches)) {
            throw new CoreException("pattern could not be auto converted to template");
        }
        return $this->render($matches["tpl"]);
    }

    public function addError() {
		$n_args = func_num_args();
		$args = func_get_args();
        if ($n_args == 2) {
            $this->errors[$args[0]] = $args[1];
        } else {
            $this->errors[] = $args[0];
        }
    }

    public function setErrors(array $data = array()) {
        // not quite a wrapper for addError - we overwrite
        // whatever was in the array instead
        $this->errors = $data;
    }

    public function getErrors() {
        return $this->errors;
    }
}
