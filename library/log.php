<?php
class Log {
	private static $handle = NULL;
	public static function debug($str, $verbose=NULL) {
		// determine log mode...
		// write log
		static::_log($str, "debug");
	}
	
	private static function _log($str, $level) {
		if (static::$handle === NULL) {
            $path = Settings::getValue("log.".$level);
            if (!file_exists($path)) {
                throw new CoreException("Logfile does not exist", CoreException::LOG_FILE_ERROR, array("path" => $path));
            }
            if (!is_writable($path)) {
                throw new CoreException("Logfile is not writable", CoreException::LOG_FILE_ERROR, array("path" => $path));
            }
			static::$handle = fopen($path, "a");
			if (!static::$handle) {
				throw new CoreException("Could not open logfile for writing", CoreException::LOG_FILE_ERROR, array("path" => $path));
			}
		}
		fwrite(static::$handle, date("d/m/Y H:i:s")." - ".$str.PHP_EOL);
	}
}
