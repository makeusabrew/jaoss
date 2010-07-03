<?php
class PathManager {
	private static $paths = array();
	
	public static function loadPath($pattern = NULL, $action = NULL, $controller = NULL, $location = NULL) {
		if (!isset($pattern)) {
			throw new CoreException("No pattern passed");
		}
		if (!isset($action)) {
			throw new CoreException("No action passed");
		}
		if (!isset($location)) {
			$trace = debug_backtrace();
			if (!isset($trace[0]["file"])) {
				throw new CoreException("No controller passed and file location not available");
			}
			$dir = dirname($trace[0]["file"]);
			$location = substr($dir, strrpos($dir, "/")+1);
		}
		if (!isset($controller)) {
			$controller = ucwords($location);
		}
		
		$path = new Path();
		$path->pattern = $pattern;
		$path->location = "apps/".$location;
		$path->controller = $controller;
		$path->action = $action;
		self::$paths[] = $path;
	}
	
	public static function loadPaths() {
		$n_args = func_num_args();
		$args = func_get_args();
		$trace = debug_backtrace();
		//var_dump($trace); die();
		foreach ($args as $path) {
			if (!is_array($path)) {
				throw new CoreException("loadPaths called without array");
			}
		
			if (count($path) == 0) {
				throw new CoreException("loadPaths called with empty array");
			}
		}
		foreach ($args as $path) {
			$pattern = isset($path[0]) ? $path[0] : NULL;
			$action = isset($path[1]) ? $path[1] : NULL;
			$controller = isset($path[2]) ? $path[2] : NULL;
			if (!isset($path[3])) {
				$dir = dirname($trace[0]["file"]);
				$location = substr($dir, strrpos($dir, "/")+1);
			} else {
				$location = $path[3];
			}
			self::loadPath($pattern, $action, $controller, $location);
		}
	}
	
	public function resetPaths() {
		self::$paths = array();
	}
	
	public static function matchUrl($url) {
		if (empty(self::$paths)) {
			throw new CoreException("No paths loaded");
		}
		
		foreach (self::$paths as $path) {
			// check for simple(r) routes
			if (substr($path->pattern, 0, 1) != "^" && substr($path->pattern, -1) != "$") {
				$path->pattern = "^{$path->pattern}$";
			}
			if (preg_match("@{$path->pattern}@", $url, $matches)) {
				$path->matches = $matches;
				return $path;
			}
		}
		// no match :(
		throw new CoreException("No matching path for URL");
	}
	
	public function getPaths() {
		return self::$paths;
	}
}
