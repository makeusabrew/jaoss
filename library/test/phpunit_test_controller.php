<?php

class PHPUnitTestController extends PHPUnit_Framework_TestCase {
    protected $request = null;
    public static function setUpBeforeClass() {
        $class = get_called_class();
        if (isset($class::$fixture_file)) {
            $user = Settings::getValue("db.user");
            $host = Settings::getValue("db.host");
            $pass = Settings::getValue("db.pass");
            $db = Settings::getValue("db.dbname");
            $cmd = "mysql -u ".($user)." -h ".($host)." -p".($pass)." --database=".($db)." < ".PROJECT_ROOT."tests/fixtures/".$class::$fixture_file.".sql";
            Log::debug("Loading fixture command [".$cmd."]");
            exec($cmd);
        }
    }

    public function setUp() {
        $this->request = new TestRequest(); 
        $session = Session::getInstance();
        $session->destroy();
    }

    public function tearDown() {
        $this->request = null;
    }

    public function assertController($controller) {
        $this->assertEquals($controller, $this->request->getResponse()->getPath()->getController());
    }

    public function assertAction($action) {
        $this->assertEquals($action, $this->request->getResponse()->getPath()->getAction());
    }

    public function assertBodyHasContents($contents) {
        $body = $this->request->getResponse()->getBody();
        $this->assertTrue((strpos($body, $contents) !== false));
    }

    public function assertRedirect($isRedirect) {
        $this->assertEquals($isRedirect, $this->request->getResponse()->isRedirect());
    }

    public function assertRedirectUrl($url) {
        $this->assertEquals($url, $this->request->getResponse()->getRedirectUrl());
    }
    
    public function assertResponseHasJsonVar($var, $val) {
        $data = json_decode($this->request->getResponse()->getBody());
        $this->assertTrue(isset($data->$var));
        $this->assertEquals($val, $data->$var);
    }
    
    public function assertResponseCode($code) {
        $this->assertEquals($code, $this->request->getResponse()->getResponseCode());
    }
}
