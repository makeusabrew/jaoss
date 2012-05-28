<?php
class AppManager {
    protected static $installed_apps = array();
    protected static $appFolders = array();
	
	public static function installApp($folder) {
		if (is_dir(PROJECT_ROOT."apps/".$folder)) {			
			$app = new App($folder);
			self::$installed_apps[] = $app;			
            self::$appFolders[] = $folder;
		}
	}
	
	public static function getInstalledApps() {
		return self::$installed_apps;
	}

    public static function getInstalledAppsHash() {
        return sha1(implode("-", self::$appFolders));
    }

    public static function loadAppPaths() {
        foreach (self::$installed_apps as $app) {
			$app->loadPaths();
        }
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

    public static function reset() {
        self::$installed_apps = array();
        self::$appFolders = array();
    }
}
