<?php
class PathManager {
	private static $paths = array();
	
	public static function loadPath($pattern = NULL, $action = NULL, $controller = NULL, $location = NULL) {
		if (!isset($pattern)) {
			throw new CoreException("No pattern passed");
		}
		if (!isset($action)) {
            $action = "templateForPattern";
		}
		if (!isset($location)) {
            $location = self::getLocationFromTrace();
		}
		if (!isset($controller)) {
			$controller = ucwords($location);
		}
		
		$path = new Path();
		$path->setPattern($pattern);
		$path->setLocation("apps/".$location);
        $path->setApp($location);
		$path->setController($controller);
		$path->setAction($action);
		self::$paths[] = $path;
		Log::verbose("Loading path: pattern [".$path->getPattern()."] location [".$path->getLocation()."] controller [".$path->getController()."] action [".$path->getAction()."]");
	}
	
	public static function loadPaths() {
		$n_args = func_num_args();
		$args = func_get_args();

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
                $location = self::getLocationFromTrace();
			} else {
				$location = $path[3];
			}
			self::loadPath($pattern, $action, $controller, $location);
		}
	}
	
    public static function loadPathsFromController($controller) {
        $location = self::getLocationFromTrace();
        $path = "apps/".$location."/controllers/".strtolower($controller).".php";
        Log::debug("looking for controller [".$path."]");
        if (!file_exists($path)) {
            throw new CoreException("file does not exist");
        }
        include_once($path);
        if (!class_exists($controller."Controller")) {
            throw new CoreException("controller class does not exist");
        }
        $reflection = new ReflectionClass($controller."Controller");
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        //@todo filter out methods declared in child class only
        //@todo map to paths
        throw new CoreException("Not Implemented");
    }

	public static function reset() {
		self::$paths = array();
	}
	
	public static function matchUrl($url) {
		if (empty(self::$paths)) {
			throw new CoreException(
				"No paths loaded"
			);
		}
		Log::verbose("Looking for match against URL [".$url."]");
		foreach (self::$paths as $path) {
			// check for simple(r) routes
            $pattern = $path->getPattern();
			if (substr($pattern, 0, 1) != "^" && substr($pattern, -1) != "$") {
				$path->setPattern("^{$pattern}$");
                $pattern = $path->getPattern();
			}
			if (preg_match("@{$pattern}@", $url, $matches)) {
				Log::debug("matched pattern [".$pattern."] against URL [".$url."]");
				$path->setMatches($matches);
				return $path;
			}
			Log::verbose("Discarding path pattern [".$pattern."]");
		}
		// no match :(
		throw new CoreException(
			"No matching path for URL",
			CoreException::URL_NOT_FOUND,
			array(
				"paths" => self::$paths,
				"url" => $url,
			)
		);
	}
	
	public static function getPaths() {
		return self::$paths;
	}

    private static function getLocationFromTrace() {
        $trace = debug_backtrace();
        if (!isset($trace[1]["file"])) {
            throw new CoreException("file location not available");
        }
        $dir = dirname($trace[1]["file"]);
        return substr($dir, strrpos($dir, "/")+1);
    }
}
