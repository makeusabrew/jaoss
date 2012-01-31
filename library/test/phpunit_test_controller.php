<?php

class PHPUnitTestController extends PHPUnit_Framework_TestCase {
    protected $request = null;
    protected $bodyPositionOffset = 0;
    public static function setUpBeforeClass() {
        self::loadFixture();
    }

    public static function loadFixture() {
        $class = get_called_class();
        if (isset($class::$fixture_file)) {
            $user = Settings::getValue("db.user");
            $host = Settings::getValue("db.host");
            $pass = Settings::getValue("db.pass");
            $db = Settings::getValue("db.dbname");
            $path = escapeshellarg(PROJECT_ROOT."tests/fixtures/".$class::$fixture_file.".sql");
            $cmd = "mysql -u ".($user)." -h ".($host)." -p".($pass)." --database=".($db)." < ".$path;
            $cmdMasked = str_replace($pass, str_repeat("*", strlen($pass)), $cmd);
            Log::debug("Loading fixture command [".$cmdMasked."]");
            $start = microtime(true);
            exec($cmd);
            $end = round(microtime(true) - $start, 2);
            Log::debug("Fixture loaded in [".$end."] seconds");
        }
    }

    public function setUp() {
        $this->request = JaossRequest::getInstance(); 
        $this->request->setProperties(array(
            "base_href" => Settings::getValue("site.base_href")
        ));

        $session = Session::getInstance();
        $session->destroy();
    }

    public function tearDown() {
        $this->request = null;
        $this->bodyPositionOffset = 0;
        PathManager::reloadPaths();
        JaossRequest::destroyInstance();
    }

    public function assertController($controller) {
        $this->assertEquals($controller, $this->request->getResponse()->getPath()->getController(), "Controller is not '{$controller}'");
    }

    public function assertAction($action) {
        $this->assertEquals($action, $this->request->getResponse()->getPath()->getAction(), "Action is not '{$action}'");
    }

    public function assertApp($app) {
        $this->assertEquals($app, $this->request->getResponse()->getPath()->getApp());
    }

    public function assertBodyHasContents($contents, $response = null) {
        $response = $response ? $response : $this->request->getResponse();
        $body = $response->getBody();
        $this->assertTrue((strpos($body, $contents) !== false), "Response missing body contents: '{$contents}'");
    }

    public function assertBodyHasContentsInOrder($contents, $response = null) {
        $response = $response ? $response : $this->request->getResponse();
        $body = $response->getBody();
        $offset = strpos($body, $contents, $this->bodyPositionOffset);
        if ($offset !== false) {
            $this->bodyPositionOffset = $offset;
        }
        $this->assertTrue(($offset !== false), "Response body does not have body contents in correct order");
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
    
    public function assertResponseCode($code, $response = null) {
        $response = $response ? $response : $this->request->getResponse();
        $this->assertEquals($code, $response->getResponseCode(), "Response Code is not '{$code}'");
    }
}
