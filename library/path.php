<?php
class Path {
	public function run($request = NULL) {
		$path = $this->location."/controllers/".strtolower($this->controller).".php";
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
				if ($init_val !== TRUE) {
					Log::debug("[".$this->controller."Controller->init() did not return true!", "-v");
					return $init_val;
				}
				Log::debug("running [".$this->controller."Controller->".$this->action."]");
				$return = call_user_func(array($controller, $this->action));
                if ($return === NULL) {
                    return $controller->render($this->action);
                }
                return $return;
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
}
