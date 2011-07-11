<?php
class Log {
	private static $handle = array();
    private static $levels = array(
        "verbose" => 10,
        "debug" => 20,
        "warn" => 30,
    );

	public static function debug($str) {
		self::_log($str, "debug");
	}

    public static function verbose($str) {
        self::_log($str, "verbose");
    }

    public static function warn($str) {
        self::_log($str, "warn");
    }

    public static function json($data) {
        self::_log(json_encode($data), "debug");
    }
	
	private static function _log($str, $logLevel) {
        $allowed = Settings::getValue("log.level", "warn");

        if (self::$levels[$logLevel] < self::$levels[$allowed]) {
            return;
        }
        foreach (self::$levels as $level => $val) {
            if ($val < self::$levels[$allowed]) {
                continue;
            }
            if ($val > self::$levels[$logLevel]) {
                break;
            }
            $path = Settings::getValue("log.".$level);
            if (!isset(self::$handle[$path])) {
                if (!file_exists($path)) {
                    // file doesn't exist, so a call to is_writable will always fail. so, we have to be a bit dirty i'm afraid :(
                    // have to surpress the E_WARNING, check the result, and then throw an error if it's failed
                    // we also need a try catch in case we're in exception throwing mode, in which case @ does naff all
                    try {
                        $file = @fopen($path, "w");
                    } catch (ErrorException $e) {
                        $file = false;
                    }
                    if ($file === false) {
                        throw new CoreException("Logfile does not exist and could not be created", CoreException::LOG_FILE_ERROR, array("path" => $path));
                    }
                    fclose($file);
                    chmod($path, 0777);
                }
                if (!is_writable($path)) {
                    throw new CoreException("Logfile is not writable", CoreException::LOG_FILE_ERROR, array("path" => $path));
                }
                self::$handle[$path] = fopen($path, "a");
                if (!self::$handle[$path]) {
                    throw new CoreException("Could not open logfile for writing", CoreException::LOG_FILE_ERROR, array("path" => $path));
                }
            }
            fwrite(self::$handle[$path], date("d/m/Y H:i:s")." - ".$str.PHP_EOL);
        }
	}
}
