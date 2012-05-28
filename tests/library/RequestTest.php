<?php
class RequestTest extends PHPUnit_Framework_TestCase {
    protected $reqData = array();

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
    }

    public function tearDown() {
    }

    public function testUrlIsSetCorrectlyInVhostMode() {
        $request = new JaossRequest($this->reqData);
        $this->assertEquals("/my/url", $request->getUrl());
    }

    public function testRequestIsCachableWhenGetAndNoQueryStringSet() {
        $request = new JaossRequest($this->reqData);
        $this->assertTrue($request->isCacheable());
    }

    public function testRequestIsCachableWhenGetQueryStringSet() {
        $this->reqData['QUERY_STRING'] = 'foo=bar&baz=test';
        $this->reqData['REQUEST_URI']  = '/my/url?foo=bar&baz=test';
        $request = new JaossRequest($this->reqData);
        $this->assertTrue($request->isCacheable());
    }

    public function testRequestIsNotCachableWhenPostAndNoQueryStringSet() {
        $this->reqData['REQUEST_METHOD'] = 'POST';
        $request = new JaossRequest($this->reqData);
        $this->assertFalse($request->isCacheable());
    }

    public function testDispatchThrowsExceptionWhenPathControllerNotFound() {
        PathManager::loadPath("/my/url", "fake_action", "Fake", "fake");
        $request = new JaossRequest($this->reqData);
        try {
            $request->dispatch();
        } catch (CoreException $e) {
            $this->assertEquals(CoreException::CONTROLLER_CLASS_NOT_FOUND, $e->getCode());
            return;
        }
        $this->fail("Expected exception not found");
    }

    public function testResponseStartsNull() {
        $request = new JaossRequest($this->reqData);
        $this->assertNull($request->getResponse());
    }

    public function testGetMethodIsCorrectWhenRequestIsGet() {
        $request = new JaossRequest($this->reqData);
        $this->assertEquals("GET", $request->getMethod());
    }

    public function testGetMethodIsCorrectWhenRequestIsPost() {
        $this->reqData['REQUEST_METHOD'] = "POST";
        $request = new JaossRequest($this->reqData);
        $this->assertEquals("POST", $request->getMethod());
    }

    public function testIsGetWhenRequestIsGet() {
        $request = new JaossRequest($this->reqData);
        $this->assertTrue($request->isGet());
    }

    public function testIsGetWhenRequestIsPost() {
        $this->reqData['REQUEST_METHOD'] = "POST";
        $request = new JaossRequest($this->reqData);
        $this->assertTrue($request->isPost());
    }

    public function testGetVarReturnsNullWhenNotFoundByDefault() {
        $request = new JaossRequest($this->reqData);
        $this->assertNull($request->getVar("fake"));
    }

    public function testGetVarReturnsCorrectDefaultValueWhenNotFound() {
        $request = new JaossRequest($this->reqData);
        $this->assertEquals("somevar", $request->getVar("fake", "somevar"));
    }

    public function testAjaxIsFalseByDefault() {
        $request = new JaossRequest($this->reqData);
        $this->assertFalse($request->isAjax());
    }

    public function testXRequestedWithEnablesAjaxMode() {
        $this->reqData['HTTP_X_REQUESTED_WITH'] = "true";
        $request = new JaossRequest($this->reqData);
        $this->assertTrue($request->isAjax());
    }

    public function testDisableAjax() {
        $this->reqData['HTTP_X_REQUESTED_WITH'] = "true";
        $request = new JaossRequest($this->reqData);
        $this->assertTrue($request->isAjax());
        $request->disableAjax();
        $this->assertFalse($request->isAjax());
    }

    public function testGetIp() {
        $request = new JaossRequest($this->reqData);
        $this->assertEquals("127.0.0.1", $request->getIp());
    }

    public function testGetHostname() {
        $request = new JaossRequest($this->reqData);
        $this->assertEquals("myproject.build", $request->getHostname());
    }

    public function testGetFolderBase() {
        $request = new JaossRequest($this->reqData);
        $this->assertEquals("/", $request->getFolderBase());
    }

    public function testGetUserAgent() {
        $request = new JaossRequest($this->reqData);
        $this->assertEquals("A Test Browser", $request->getUserAgent());
    }

    public function testGetTimestamp() {
        $request = new JaossRequest($this->reqData);
        $this->assertEquals(1317303304, $request->getTimestamp());
    }

    public function testGetSapi() {
        $request = new JaossRequest($this->reqData);
        $this->assertEquals("cli", $request->getSapi());
    }

    public function testGetQueryString() {
        $request = new JaossRequest($this->reqData);
        $this->assertEquals("foo=bar", $request->getQueryString());

        unset($this->reqData["QUERY_STRING"]);

        $request = new JaossRequest($this->reqData);
        $this->assertEquals(null, $request->getQueryString());
    }

    public function testCacheDisabled() {
        $request = new JaossRequest($this->reqData);

        $this->assertFalse($request->isCacheDisabled());

        $request->disableCache();

        $this->assertTrue($request->isCacheDisabled());
    }

    public function testGetFile() {
        $request = new JaossRequest($this->reqData);

        $this->assertNull($request->getFile("invalid"));

        $_FILES['foo'] = 'bar';

        $this->assertEquals('bar', $request->getFile("foo"));

        unset($_FILES['foo']);
    }

    public function testProcessFile() {
        $request = new JaossRequest($this->reqData);

        $file = $request->processFile("invalid");
        $this->assertTrue($file instanceof File);

        $this->assertEquals(99, $file->getError());
    }

    public function testGetHeader() {
        $request = new JaossRequest($this->reqData);

        $this->assertNull($request->getHeader('invalid'));
        $this->assertEquals('bar', $request->getHeader('foo'));
    }

    public function testGetBaseHref() {
        $request = new JaossRequest($this->reqData);
        $this->assertEquals('http://myproject.build/', $request->getBaseHref());
    }

    public function testGetBaseHrefHttpNonStandardPort() {
        $this->reqData['SERVER_PORT'] = '8080';
        $request = new JaossRequest($this->reqData);
        $this->assertEquals('http://myproject.build:8080/', $request->getBaseHref());
    }

    public function testGetBaseHrefHttps() {
        $this->reqData['SSL'] = 'on';
        $this->reqData['SERVER_PORT'] = '443';
        $request = new JaossRequest($this->reqData);
        $this->assertEquals('https://myproject.build/', $request->getBaseHref());
    }

    public function testGetBaseHrefHttpsNonStandardPort() {
        $this->reqData['SSL'] = 'on';
        $this->reqData['SERVER_PORT'] = '4444';
        $request = new JaossRequest($this->reqData);
        $this->assertEquals('https://myproject.build:4444/', $request->getBaseHref());
    }

    public function testGetFullUrl() {
        $request = new JaossRequest($this->reqData);
        $this->assertEquals("http://myproject.build/my/url", $request->getFullUrl());
    }
}
