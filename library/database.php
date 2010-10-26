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
            Log::debug("Instantiating db dsn [".$dsn."] user [".Settings::getValue("db", "user")."]");
			self::$instance = new Db(
                $dsn,
				Settings::getValue("db", "user"),
				Settings::getValue("db", "pass")
			);
			
			self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		}
		
		return self::$instance;
	}

}
