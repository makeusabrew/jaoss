<?php
class Settings {
    private static $mode = null;
    private static $modes = array(
        "live", "demo", "build", "test"
    );
	
	private static $settings = array();
	
	public static function loadFromFile($file) {
		if (!is_readable($file)) {
			throw new CoreException("File is not readable");
		}
        $newSettings = parse_ini_file($file, true);
        foreach($newSettings as $group => $settings) {
            if (isset(self::$settings[$group])) {
                self::$settings[$group] = array_merge(self::$settings[$group], $settings);
            } else {
                self::$settings[$group] = $settings;
            }
        }
	}	

    public static function loadStandardSettings() {
        $loadStr = "Loaded ";
        foreach (self::$modes as $mode) {
            try {
                self::loadFromFile(PROJECT_ROOT."settings/".$mode.".ini");
                $loadStr .= "{$mode},";
            } catch (CoreException $e) {
                // don't worry about it, could log
            }
            if (self::$mode == $mode) {
                break;  // we're done
            }
        }

        $loadStr = substr($loadStr, 0, -1)." settings";
        Log::debug($loadStr);
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

    public static function setMode($mode) {
        if (!in_array($mode, self::$modes)) {
            throw new CoreException("Mode is not supported",
                CoreException::INVALID_MODE,
                array(
                    "mode" => $mode,
                    "modes" => self::$modes,
                )
            );
        }
        self::$mode = $mode;
    }

    public static function getMode() {
        return self::$mode;
    }
    
    public static function reset() {
        self::$settings = array();
        self::$mode = null;
    }
}
