<?php
require_once("PHPUnit/Extensions/SeleniumTestCase.php");

class SeleniumTestController extends PHPUnit_Extensions_SeleniumTestCase {
    public function setUp() {
        $class = get_called_class();
        if (isset($class::$fixture_file)) {
            $user = Settings::getValue("db.user");
            $host = Settings::getValue("db.host");
            $pass = Settings::getValue("db.pass");
            $db = Settings::getValue("db.dbname");
            $path = escapeshellarg(PROJECT_ROOT."tests/fixtures/".$class::$fixture_file.".sql");
            $cmd = "mysql -u ".($user)." -h ".($host)." -p".($pass)." --database=".($db)." < ".$path;
            $cmdMasked = str_replace($pass, str_repeat("*", strlen($pass)), $cmd);
            Log::debug("Loading fixture command [".$cmd."]");
            $start = microtime(true);
            exec($cmd);
            $end = round(microtime(true) - $start, 2);
            Log::debug("Fixture loaded in [".$end."] seconds");
        }
    }

    public function open($url, $maximize = true) {
        parent::open($url);
        if ($maximize) {
            $this->windowMaximize();
        }
    }
}
