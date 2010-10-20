<?php
class Settings {
	private static $mode = "build";
	
	private static $settings = array();
	
	public static function loadFromFile($file) {
		if (!is_readable($file)) {
			throw new CoreException("File is not readable");
		}
		self::$settings = array_merge(self::$settings, parse_ini_file($file, TRUE));
	}	
	
	public static function getValue($section, $key=NULL) {
		if ($key === NULL) {
			// okay, key hasn't been passed, so assume we've gone shorthand
			list($section, $key) = explode(".", $section);
		}
		if (!isset(self::$settings[$section][$key])) {
            Log::debug("setting [$section] [$key] does not exist");
			throw new CoreException("Setting not found");
		}
		return self::$settings[$section][$key];
	}
	
	public static function getSettings($section=NULL) {
		return $section ? self::$settings[$section] : self::$settings;
	}

    public static function setFromArray($settings) {
        self::$settings = $settings;
    }
}
