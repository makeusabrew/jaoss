<?php
class Path {
    protected $pattern;
    protected $location;
    protected $app;
    protected $controller;
    protected $action;
    protected $matches;

	public function run($request = NULL) {
		$path = PROJECT_ROOT.$this->location."/controllers/".strtolower($this->controller).".php";
		if (!file_exists($path)) {
			throw new CoreException("Controller file does not exist");
		}
		require_once($path);
		$controller = $this->controller."Controller";
		if (!class_exists($controller)) {
			throw new CoreException("Controller class does not exist");
		}
		$controller = new $controller($request);
		if (method_exists($controller, $this->action)) {
			if (is_callable(array($controller, $this->action))) {
				$controller->setPath($this);
				$init_val = $controller->init();
				if ($init_val !== "OK") {
					Log::debug($this->controller."Controller->init() did not return status [OK]!", "-v");
                    return $controller->getResponse();
				}
				Log::debug("running [".$this->controller."Controller->".$this->action."]");
				$result = call_user_func(array($controller, $this->action));
                if ($result === NULL) {
                    $controller->render($this->action);
                }
                return $controller->getResponse();
			} else {
				throw new CoreException("Controller action is not callable");
			}
		} else {
			throw new CoreException(
				"Controller action does not exist",
				CoreException::ACTION_NOT_FOUND,
				array(
					"controller" => get_class($controller),
					"action" => $this->action,
					"path" => $path,
				)
			);
		}
	}

    public function setPattern($pattern) {
        $this->pattern = $pattern;
    }

    public function setLocation($location) { 
        $this->location = $location;
    }

    public function setAction($action) {
        $this->action = $action;
    }

    public function setController($controller) {
        $this->controller = $controller;
    }

    public function setApp($app) {
        $this->app = $app;
    }

    public function getPattern() {
        return $this->pattern;
    }

    public function getLocation() {
        return $this->location;
    }

    public function getAction() {
        return $this->action;
    }

    public function getController() {
        return $this->controller;
    }

    public function getApp() {
        return $this->app;
    }

    public function setMatches($matches) {
        $this->matches = $matches;
    }
}
