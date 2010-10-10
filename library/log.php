<?php
class Log {
	private static $handle = array();
	public static function debug($str) {
		self::_log($str, "debug");
	}

    public static function verbose($str) {
        self::_log($str, "verbose");
    }
	
	private static function _log($str, $level) {
        $handler = Settings::getValue("log.".$level);
		if (!isset(self::$handle[$handler])) {
            $path = Settings::getValue("log", $handler);
            if (!file_exists($path)) {
                throw new CoreException("Logfile does not exist", CoreException::LOG_FILE_ERROR, array("path" => $path));
            }
            if (!is_writable($path)) {
                throw new CoreException("Logfile is not writable", CoreException::LOG_FILE_ERROR, array("path" => $path));
            }
			self::$handle[$handler] = fopen($path, "a");
			if (!self::$handle) {
				throw new CoreException("Could not open logfile for writing", CoreException::LOG_FILE_ERROR, array("path" => $path));
			}
		}
		fwrite(self::$handle[$handler], date("d/m/Y H:i:s")." - ".$str.PHP_EOL);
	}
}
