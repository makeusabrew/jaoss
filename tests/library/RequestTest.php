<?php
class RequestTest extends PHPUnit_Framework_TestCase {
    protected $reqData = array();
    protected $request = null;

    public function setUp() {
        $this->reqData = array (
            'HTTP_HOST' => 'myproject.build',
            'HTTP_USER_AGENT' => 'A Test Browser',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_REFERER' => 'http://anothersite.com/',
            'HTTP_ACCEPT_ENCODING' => 'gzip,deflate,sdch',
            'HTTP_ACCEPT_LANGUAGE' => 'en-GB,en-US;q=0.8,en;q=0.6',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
            'SERVER_NAME' => 'myproject.build',
            'SERVER_ADDR' => '127.0.0.1',
            'SERVER_PORT' => '80',
            'REMOTE_ADDR' => '127.0.0.1',
            'DOCUMENT_ROOT' => '/var/www/foo/bar/public',
            'SERVER_ADMIN' => '[no address given]',
            'SCRIPT_FILENAME' => '/var/www/foo/bar/public/index.php',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'foo=bar',
            'REQUEST_URI' => '/my/url?foo=bar',
            'SCRIPT_NAME' => '/index.php',
            'PHP_SELF' => '/index.php',
            'REQUEST_TIME' => 1317303304,
            '_headers' => array(
                'foo' => 'bar',
            ),
        );

        $this->populateRequest();
    }

    protected function populateRequest() {
        $this->request = new JaossRequest();
        $this->request->setProperties($this->reqData);
    }

    public function testUrlIsSetCorrectlyInVhostMode() {
        $this->assertEquals("/my/url", $this->request->getUrl());
    }

    public function testRequestIsCachableWhenGetAndNoQueryStringSet() {
        $this->assertTrue($this->request->isCacheable());
    }

    public function testRequestIsCachableWhenGetQueryStringSet() {
        $this->reqData['QUERY_STRING'] = 'foo=bar&baz=test';
        $this->reqData['REQUEST_URI']  = '/my/url?foo=bar&baz=test';

        $this->populateRequest();

        $this->assertTrue($this->request->isCacheable());
    }

    public function testRequestIsNotCachableWhenPostAndNoQueryStringSet() {
        $this->reqData['REQUEST_METHOD'] = 'POST';

        $this->populateRequest();

        $this->assertFalse($this->request->isCacheable());
    }

    public function testDispatchThrowsExceptionWhenPathControllerNotFound() {
        PathManager::loadPath("/my/url", "fake_action", "Fake", "fake");
        try {
            $this->request->dispatch();
        } catch (CoreException $e) {
            $this->assertEquals(CoreException::CONTROLLER_CLASS_NOT_FOUND, $e->getCode());
            return;
        }
        $this->fail("Expected exception not found");
    }

    public function testResponseStartsNull() {
        $this->assertNull($this->request->getResponse());
    }

    public function testGetMethodIsCorrectWhenRequestIsGet() {
        $this->assertEquals("GET", $this->request->getMethod());
    }

    public function testGetMethodIsCorrectWhenRequestIsPost() {
        $this->reqData['REQUEST_METHOD'] = "POST";

        $this->populateRequest();

        $this->assertEquals("POST", $this->request->getMethod());
    }

    public function testIsGetWhenRequestIsGet() {
        $this->assertTrue($this->request->isGet());
    }

    public function testIsGetWhenRequestIsPost() {
        $this->reqData['REQUEST_METHOD'] = "POST";
        
        $this->populateRequest();

        $this->assertTrue($this->request->isPost());
    }

    public function testGetVarReturnsNullWhenNotFoundByDefault() {
        $this->assertNull($this->request->getVar("fake"));
    }

    public function testGetVarReturnsCorrectDefaultValueWhenNotFound() {
        $this->assertEquals("somevar", $this->request->getVar("fake", "somevar"));
    }

    public function testAjaxIsFalseByDefault() {
        $this->assertFalse($this->request->isAjax());
    }

    public function testXRequestedWithEnablesAjaxMode() {
        $this->reqData['HTTP_X_REQUESTED_WITH'] = "true";

        $this->populateRequest();

        $this->assertTrue($this->request->isAjax());
    }

    public function testDisableAjax() {
        $this->reqData['HTTP_X_REQUESTED_WITH'] = "true";

        $this->populateRequest();

        $this->assertTrue($this->request->isAjax());
        $this->request->disableAjax();
        $this->assertFalse($this->request->isAjax());
    }

    public function testGetIp() {
        $this->assertEquals("127.0.0.1", $this->request->getIp());
    }

    public function testGetHostname() {
        $this->assertEquals("myproject.build", $this->request->getHostname());
    }

    public function testGetFolderBase() {
        $this->assertEquals("/", $this->request->getFolderBase());
    }

    public function testGetUserAgent() {
        $this->assertEquals("A Test Browser", $this->request->getUserAgent());
    }

    public function testGetTimestamp() {
        $this->assertEquals(1317303304, $this->request->getTimestamp());
    }

    public function testGetSapi() {
        $this->assertEquals("cli", $this->request->getSapi());
    }

    public function testGetQueryString() {
        $this->assertEquals("foo=bar", $this->request->getQueryString());

        unset($this->reqData["QUERY_STRING"]);

        $this->populateRequest();

        $this->assertEquals(null, $this->request->getQueryString());
    }

    public function testCacheDisabled() {
        $this->assertFalse($this->request->isCacheDisabled());

        $this->request->disableCache();

        $this->assertTrue($this->request->isCacheDisabled());
    }

    public function testGetFile() {
        $this->assertNull($this->request->getFile("invalid"));

        $_FILES['foo'] = 'bar';

        $this->assertEquals('bar', $this->request->getFile("foo"));

        unset($_FILES['foo']);
    }

    public function testProcessFile() {
        $file = $this->request->processFile("invalid");
        $this->assertTrue($file instanceof File);

        $this->assertEquals(99, $file->getError());
    }

    public function testGetHeader() {
        $this->assertNull($this->request->getHeader('invalid'));
        $this->assertEquals('bar', $this->request->getHeader('foo'));
    }

    public function testGetBaseHref() {
        $this->assertEquals('http://myproject.build/', $this->request->getBaseHref());
    }

    public function testGetBaseHrefHttpNonStandardPort() {
        $this->reqData['SERVER_PORT'] = '8080';

        $this->populateRequest();

        $this->assertEquals('http://myproject.build:8080/', $this->request->getBaseHref());
    }

    public function testGetBaseHrefHttps() {
        $this->reqData['SSL'] = 'on';
        $this->reqData['SERVER_PORT'] = '443';

        $this->populateRequest();

        $this->assertEquals('https://myproject.build/', $this->request->getBaseHref());
    }

    public function testGetBaseHrefHttpsNonStandardPort() {
        $this->reqData['SSL'] = 'on';
        $this->reqData['SERVER_PORT'] = '4444';

        $this->populateRequest();

        $this->assertEquals('https://myproject.build:4444/', $this->request->getBaseHref());
    }

    public function testGetFullUrl() {
        $this->assertEquals("http://myproject.build/my/url", $this->request->getFullUrl());
    }
}
