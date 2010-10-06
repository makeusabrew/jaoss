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

            Log::debug("Instantiating db");
			self::$instance = new Db(
				Settings::getValue("db", "dsn"),
				Settings::getValue("db", "user"),
				Settings::getValue("db", "pass")
			);
			
			self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		}
		
		return self::$instance;
	}

}
