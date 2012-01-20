<?php
class Db extends PDO {

    /**
    * @var Db Singleton instance
    */
    private static $instance = NULL;

    /**
    * Get the singleton instance
    * @return Db
    */
    public static function getInstance() {
        if (self::$instance === NULL) {

            $dsn = "mysql:dbname=".Settings::getValue("db", "dbname").";host=".Settings::getValue("db", "host");
            Log::verbose("Instantiating db dsn [".$dsn."] user [".Settings::getValue("db", "user")."]");

            $options = array();
            if (Settings::getValue("db", "encoding", false) == "utf8") {
                $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'UTF8'";
            }

            self::$instance = new Db(
                $dsn,
                Settings::getValue("db", "user"),
                Settings::getValue("db", "pass"),
                $options
            );

            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }

        return self::$instance;
    }
}
