<?php
abstract class Controller {
    protected $smarty    = null;
    protected $path      = null;
    protected $adminUser = null;
    protected $session   = null;
    protected $request   = null;
    protected $response  = null;
    protected $errors    = array();
    protected $var_stack = array();

    public function init() {
        // this method can be overridden - we don't want to declare it
        // abstract since it doesn't *have* to be, so it's left empty
    }

    public function __construct($request = NULL) {
        $this->smarty = new Smarty();

        $tpl_dirs = array(PROJECT_ROOT."apps/");
		
        $plugin_dirs = array(
            JAOSS_ROOT."library/Smarty/libs/plugins",  // default smarty dir
            JAOSS_ROOT."library/smarty_plugins",       // our own library extensions
        );

        // allow site specific plugin dirs - these should take precedence if present
        if (($sitePlugins = Settings::getValue("smarty", "plugins", false)) !== false) {
            $plugin_dirs = array_merge(
                $sitePlugins,
                $plugin_dirs
            );
        }

        $this->smarty->setTemplateDir($tpl_dirs)
                     ->setCompileDir(Settings::getValue("smarty", "compile_dir"))
                     ->setPluginsDir($plugin_dirs);

        // no setter for this, strangely...
        $this->smarty->compile_check = Settings::getBool("smarty", "compile_check");

        $this->request = $request;
        $this->response = new JaossResponse();
		
        $this->session = Session::getInstance();
    }
	
    public function setPath($path) {
        $this->path = $path;
        $this->response->setPath($path);

        if ($this->smarty !== null) {
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
                self::includeController($app_path, $controller);
            } else {
                $apps = AppManager::getAppPaths();
                foreach ($apps as $app) {
                    if (self::includeController($app, $controller)) {
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
                "class"      => $c_class,
                "path"       => isset($app_path) ? $app_path : null,
                "apps"       => isset($apps) ? $apps : null,
            )
        );
    }

    protected static function includeController($app, $controller) {
        $path = PROJECT_ROOT."apps/{$app}/controllers/".Utils::fromCamelCase($controller).".php";
        if (file_exists($path)) {
            include($path);
            return true;
        }
        return false;
    }
	
    public function getMatch($match, $default=NULL) {
        if (!$this->path->hasMatch($match)) {
            return $default;
        }
        return $this->path->getMatch($match);
    }

    protected function resolveUrl($args, $full = false) {
        if (!isset($args["name"])) {
            if (!isset($args["controller"])) {
                $args["controller"] = $this->path->getController();
            }
            if (!isset($args["app"])) {
                $args["app"] = $this->path->getApp();
            }
        }
        $url = PathManager::getUrlForOptions($args);
        if ($full === true) {
            $url = substr($this->request->getBaseHref(), 0, -1).$url;
        }
        return $url;
    }

    protected function resolveFullUrl($args) {
        return $this->resolveUrl($args, true);
    }

    public function redirect($url, $message = NULL) {
        if (is_array($url)) {
            $url = $this->resolveUrl($url);
        }
    	if ($this->request->isAjax()) {
    		$this->assign("redirect", $url);

            // assume AJAX handlers won't follow a redirect, so assign
            // the message directly instead
            if ($message) {
                $this->assign("message", $message);
            }
            return $this->renderJson();
    	} else {
            // bung a message in the session
            if ($message) {
                FlashMessenger::addMessage($message);
            }
            // check for subfolder mode
            if ($this->request->getFolderBase() !== '/' && $url{0} === '/') {
                $url = substr($this->request->getBaseHref(), 0, -1).$url;
            }
            $this->response->setRedirect($url, 303);
            return $this->response;
        }
    }

    public function redirectAction($action, $message = NULL) {
        return $this->redirect(array("action" => $action), $message);
    }

    public function redirectName($name, $message = NULL) {
        return $this->redirect(array("name" => $name), $message);
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

        if ($this->request->isPjax()) {
            return $this->renderPjax($template);
        }

        if ($this->request->isAjax()) {
            return $this->renderJson();
        }

        return $this->renderTemplate($template);
    }

    public function renderPjax($template) {
        $this->assign('_pjax', true);
        return $this->renderTemplate($template);
    }
	
    /**
     * render functions always render to the response body
     */
    public function renderJson($extra = array()) {
        $this->response->addHeader('Content-Type', 'application/json');
        $this->response->setBody($this->fetchJson($extra));
        return $this->response;
    }

    public function renderTemplate($template) {
        if ($this->response->getHeader('Content-Type') === null) {
            $this->response->addHeader('Content-Type', 'text/html; charset=utf-8');
        }
        $this->response->setBody(
            $this->fetchTemplate($template)
        );
        return $this->response;
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

    public function fetchTemplate($template, $extra = array()) {
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

        foreach ($extra as $var => $val) {
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
        return $this;
    }

    public function getFlash($flash) {
       return $this->session->getFlash($flash);
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

    public function getResponse() {
        return $this->response;
    }

    public function filterRequest() {
        $final = array();
        foreach (func_get_args() as $key) {
            if ($this->request->getVar($key) !== null) {
                $final[$key] = $this->request->getVar($key);
            }
        }
        return $final;
    }

    public function filterRequestStrict() {
        $final = array();
        foreach (func_get_args() as $key) {
            $final[$key] = $this->request->getVar($key);
        }
        return $final;
    }
}
