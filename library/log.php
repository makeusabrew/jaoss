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
			static::$handle = fopen(Settings::getValue("log.".$level), "a");
			if (!static::$handle) {
				throw new CoreException("Could not open logfile for writing");
			}
		}
		fwrite(static::$handle, date("d/m/Y H:i:s")." - ".$str.PHP_EOL);
	}
}
