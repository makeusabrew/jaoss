<?php
abstract class Controller {
	protected $smarty = NULL;
	protected $path = NULL;
	protected $adminUser = NULL;
	protected $session = NULL;
	protected $base_href = NULL;
	
	public function __construct() {
		require_once("library/Smarty-3.0rc1/libs/Smarty.class.php");
		
		$this->smarty = new Smarty();
		
		$apps = AppManager::getAppPaths();
		$tpl_dirs = array("apps/");
		foreach ($apps as $app) {
			$tpl_dirs[] = "apps/{$app}/views/";
		}
		
		$this->smarty->template_dir	= $tpl_dirs;
		$this->smarty->compile_dir = Settings::getValue("smarty", "compile_dir");
		
		$this->base_href = "http://".$_SERVER["SERVER_NAME"].substr($_SERVER["PHP_SELF"], 0, strpos($_SERVER["PHP_SELF"], "index.php"));
		$this->assign("base_href", $this->base_href);
	}
	
	public function setPath($path) {
		$this->path = $path;
	}
	
	public static function factory($controller, $app_path = NULL) {
		$c_class = $controller."Controller";
		if (class_exists($c_class)) {
			return new $c_class;
		}
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
	
	public function getMatch($match) {
		if (!isset($this->path->matches[$match])) {
			throw new CoreException("No matching path index");
		}
		return $this->path->matches[$match];
	}
	
	public function render($template) {
		return $this->smarty->fetch($template.".tpl");
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
		$this->smarty->assign($var, $value);
	}
}
