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
            $path = escapeshellarg(PROJECT_ROOT."tests/fixtures/".$class::$fixture_file.".sql");
            $cmd = "mysql -u ".($user)." -h ".($host)." -p".($pass)." --database=".($db)." < ".$path;
            exec($cmd);
            $cmd = str_replace($pass, str_repeat("*", strlen($pass)), $cmd);
            Log::debug("Loading fixture command [".$cmd."]");
        }
    }

    public function setUp() {
        $this->request = new TestRequest(); 
        $session = Session::getInstance();
        $session->destroy();
    }

    public function tearDown() {
        $this->request = null;
        PathManager::reloadPaths();
    }

    public function assertController($controller) {
        $this->assertEquals($controller, $this->request->getResponse()->getPath()->getController(), "Conroller is not '{$controller}'");
    }

    public function assertAction($action) {
        $this->assertEquals($action, $this->request->getResponse()->getPath()->getAction(), "Action is not '{$action}'");
    }

    public function assertApp($app) {
        $this->assertEquals($app, $this->request->getResponse()->getPath()->getApp());
    }

    public function assertBodyHasContents($contents) {
        $body = $this->request->getResponse()->getBody();
        $this->assertTrue((strpos($body, $contents) !== false), "Response missing body contents: '{$contents}'");
    }

    public function assertBodyDoesNotHaveContents($contents) {
        $body = $this->request->getResponse()->getBody();
        $this->assertFalse((strpos($body, $contents) !== false), "Response should NOT have body contents: '{$contents}'");
    }

    public function assertRedirect($isRedirect) {
        $this->assertEquals($isRedirect, $this->request->getResponse()->isRedirect(), "Response is not redirect");
    }

    public function assertRedirectUrl($url) {
        $this->assertEquals($url, $this->request->getResponse()->getRedirectUrl(), "Redirect URL is not '{$url}'");
    }
    
    public function assertResponseHasJsonVar($var, $val) {
        $data = json_decode($this->request->getResponse()->getBody());
        $this->assertTrue(isset($data->$var));
        $this->assertEquals($val, $data->$var);
    }
    
    public function assertResponseCode($code) {
        $this->assertEquals($code, $this->request->getResponse()->getResponseCode(), "Response Code is not '{$code}'");
    }
}
