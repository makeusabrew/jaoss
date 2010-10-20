<?php
class AppManager {
	private static $installed_apps = array();
	
	public static function loadAppFromPath($folder) {
		if (is_dir(PROJECT_ROOT."apps/".$folder)) {			
			$app = new App($folder);
			$app->loadPaths();
			self::$installed_apps[] = $app;			
		}
	}
	
	public static function getInstalledApps() {
		return self::$installed_apps;
	}
	
	public static function getAppPaths() {
		$_apps = array();
		foreach (self::$installed_apps as $app) {
			$_apps[] = $app->getFolder();
		}
		return $_apps;
	}
	
	public static function isAppInstalled($app) {
		return in_array($app, self::$installed_apps);
	}
}
