<?php
class Log {
	protected static $handle = array();
    protected static $paths  = array();
    protected static $levels = array(
        "verbose" => 10,
        "debug"   => 20,
        "info"    => 30,
        "warn"    => 40,
    );
    protected static $level = null;

	public static function debug($str) {
		self::_log($str, "debug");
	}

	public static function info($str) {
		self::_log($str, "info");
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
        if (self::$level === null) {
            self::$level = Settings::getValue("log.level", "warn");
        }

        // don't do anything if the level we're trying to log at is
        // less than our current output level (e.g. debug < warn)
        if (self::$levels[$logLevel] < self::$levels[self::$level]) {
            return;
        }

        // save doing this call each time inside the loop
        $timestamp = date("d/m/Y H:i:s");

        foreach (self::$levels as $level => $val) {
            // don't bother logging if this level is less than the one
            // we're trying to log at (e.g. verbose, but we want to log at debug)
            if ($val < self::$levels[self::$level]) {
                continue;
            }

            // if we've passed the current output level, give up
            if ($val > self::$levels[$logLevel]) {
                break;
            }

            if (!isset(self::$paths[$level])) {
                self::$paths[$level] = Settings::getValue("log.".$level);
            }
            $path = self::$paths[$level];

            if (!isset(self::$handle[$path])) {
                if (!file_exists($path)) {

                    // surpress the error otherwise we'll throw an exception
                    $file = @fopen($path, "w");

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
            $dbgOut = "(".strtoupper($logLevel).")";
            $dbgOut = str_pad($dbgOut, 9);
            fwrite(self::$handle[$path], $timestamp." ".$dbgOut." - ".$str.PHP_EOL);
        }
	}
}
