<?php
class Path {
	public function run() {
		$path = $this->location."/controllers/".strtolower($this->controller).".php";
		if (!file_exists($path)) {
			throw new CoreException("Controller file does not exist");
		}
		require_once($path);
		$controller = $this->controller."Controller";
		if (!class_exists($controller)) {
			throw new CoreException("Controller class does not exist");
		}
		$controller = new $controller();
		if (method_exists($controller, $this->action)) {
			if (is_callable(array($controller, $this->action))) {
				$controller->setPath($this);
				return call_user_func(array($controller, $this->action));
			} else {
				throw new CoreException("Controller action is not callable");
			}
		} else {
			throw new CoreException("Controller action does not exist");
		}
	}
}
