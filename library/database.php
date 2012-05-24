<?php
abstract class Db {

    /**
    * @var Db Singleton instances
    */
    private static $instances = NULL;

    /**
    * Get the singleton instance
    * @return Db
    */
    public static function getInstance($class = null) {
        if ($class === null) {
            $class = Settings::getValue("db", "handler", "PDO");
        }

        if (!isset(self::$instances[$class])) {

            $dsn = "mysql:dbname=".Settings::getValue("db", "dbname").";host=".Settings::getValue("db", "host");

            // charset?
            if (($charset = Settings::getValue("db", "charset", false)) != false) {
                $dsn .=";charset=".$charset;
            }
            
            // custom port?
            if (($port = Settings::getValue("db", "port", false)) != false) {
                $dsn .=";port=".$port;
            }
            Log::verbose("Instantiating db dsn [".$dsn."] user [".Settings::getValue("db", "user")."]");

            if ($class !== 'PDO') {
                require_once("library/db/".strtolower($class).".php");
            }

            self::$instances[$class] = new $class(
                $dsn,
                Settings::getValue("db", "user"),
                Settings::getValue("db", "pass")
            );

            self::$instances[$class]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }

        return self::$instances[$class];
    }
}
