<?php
class Log {
    protected static $handle = array();
    protected static $paths  = array();
    protected static $levels = array(
        "verbose" => array(
            "priority" => 10,
            "cascade"  => true,
        ),
        "debug"   => array(
            "priority" => 20,
            "cascade"  => true,
        ),
        "info"    => array(
            "priority" => 30,
            "cascade"  => true,
        ),
        "db"      => array(
            "priority" => 40,
            "cascade"  => false,
        ),
        "warn"    => array(
            "priority" => 50,
            "cascade"  => true,
        ),
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

    public static function db($data) {
        self::_log($data, "db");
    }
    
    private static function _log($str, $logLevel) {
        if (self::$level === null) {
            self::$level = Settings::getValue("log.level", "warn");
        }

        // don't do anything if the level we're trying to log at is
        // less than our current output level (e.g. debug < warn)
        if (self::$levels[$logLevel]['priority'] < self::$levels[self::$level]['priority']) {
            return;
        }

        // track anything above debug
        if (self::$levels[$logLevel]['priority'] > self::$levels["debug"]['priority']) {
            StatsD::increment("log.".$logLevel);
        }

        // save doing this call each time inside the loop
        $timestamp = date("d/m/Y H:i:s");


        $cascade = self::$levels[$logLevel]['cascade'];

        if ($cascade === false) {
            return self::writeLog($logLevel, $logLevel, $timestamp, $str);
        }
            
        foreach (self::$levels as $level => $val) {
            // don't bother logging if this level is less than the one
            // we're trying to log at (e.g. verbose, but we want to log at debug)
            if ($val['priority'] < self::$levels[self::$level]['priority']) {
                continue;
            }

            // don't log if this level doesn't support cascading
            if ($val['cascade'] === false) {
                continue;
            }

            // if we've passed the current output level, give up
            if ($val['priority'] > self::$levels[$logLevel]['priority']) {
                break;
            }

            self::writeLog($level, $logLevel, $timestamp, $str);

        }
    }

    protected static function writeLog($level, $logLevel, $timestamp, $str) {
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
