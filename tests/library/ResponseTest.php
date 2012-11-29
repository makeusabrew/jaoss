<?php
class ResponseTest extends PHPUnit_Framework_TestCase {
    //
    protected $response = null;

    public function setUp() {
        $this->response = new JaossResponse();
    }

    public function testSetAndGetBody() {
        $this->assertEquals('', $this->response->getBody());

        $this->response->setBody("Foo Bar");

        $this->assertEquals("Foo Bar", $this->response->getBody());
    }

    public function testSetAndGetResponseCode() {
        $this->assertEquals(200, $this->response->getResponseCode());

        $this->response->setResponseCode(404);

        $this->assertEquals(404, $this->response->getResponseCode());
    }

    public function testSetRedirectWithDefaultArgs() {
        $this->assertFalse($this->response->isRedirect());

        $this->response->setRedirect("/foo");

        $this->assertTrue($this->response->isRedirect());
        $this->assertEquals("/foo", $this->response->getRedirectUrl());
        $this->assertEquals(303, $this->response->getResponseCode());
    }

    public function testSetRedirectWithCustomResponseCode() {

        $this->response->setRedirect("/bar", 301);

        $this->assertTrue($this->response->isRedirect());
        $this->assertEquals("/bar", $this->response->getRedirectUrl());
        $this->assertEquals(301, $this->response->getResponseCode());
    }

    public function testDefaultGetHeaderString() {
        $this->assertEquals("HTTP/1.1 200 OK", $this->response->getHeaderString());
    }

    public function test404NotFoundHeaderString() {
        $this->response->setResponseCode(404);
        $this->assertEquals("HTTP/1.1 404 Not Found", $this->response->getHeaderString());
    }

    public function testGetHeaders() {
        $this->assertEquals(array(), $this->response->getHeaders()); 

        $this->response->addHeader('foo', 'bar');

        $this->assertEquals(array('foo' => 'bar'), $this->response->getHeaders());
    }

    public function testAddAndGetHeader() {
        $this->assertNull($this->response->getHeader('Foo'));

        $this->response->addHeader('Foo', 'bar');

        $this->assertEquals('bar', $this->response->getHeader('Foo'));
    }

    public function testGetEtag() {

        $this->response->setBody("Testing");

        $this->assertEquals(
            '"'.sha1("_Testing").'"',
            $this->response->getETag()
        );
    }

    public function testInitialisedWithNewResponse() {
        $this->assertFalse($this->response->isInitialised());
    }

    public function testInitialisedWithBody() {
        $this->response->setBody("foo");

        $this->assertTrue($this->response->isInitialised());
    }

    public function testInitialisedWithRedirectUrl() {
        $this->response->setRedirect("/");

        $this->assertTrue($this->response->isInitialised());
    }

    public function testInitialisedWithHeadersArray() {
        $this->response->addHeader("foo", "bar");

        $this->assertTrue($this->response->isInitialised());
    }

    public function testToArrayWithEmptyResponse() {
        $this->assertEquals(array(
            "body"         => null,
            "isRedirect"   => false,
            "redirectUrl"  => null,
            "responseCode" => 200,
            "path"         => null,
            "headers"      => array(),
            "ifNoneMatch"  => null,
        ), $this->response->toArray());
    }

    public function testToArrayWithEmptyPath() {
        $path = new JaossPath();
        $this->response->setPath($path);

        $this->assertEquals(array(
            "body"         => null,
            "isRedirect"   => false,
            "redirectUrl"  => null,
            "responseCode" => 200,
            "path"         => array(
                "pattern" => null,
                "app" => null,
                "controller" => null,
                "action" => null,
                "name" => null,
                "cacheTtl" => null,
                "requestMethods" => array(),
            ),
            "headers"      => array(),
            "ifNoneMatch"  => null,
        ), $this->response->toArray());
    }

    public function testToArrayWithPopulatedResponse() {
        $path = new JaossPath();
        $path->setApp("foo");
        $path->setController("bar");
        $path->setAction("baz");

        $this->response->addHeader("Content-Type", "text/html");
        $this->response->setPath($path);
        $this->response->setBody("body");
        $this->response->setRedirect("/foo/bar");

        $this->assertEquals(array(
            "body"         => "body",
            "isRedirect"   => true,
            "redirectUrl"  => "/foo/bar",
            "responseCode" => 303,
            "path"         => array(
                "pattern" => null,
                "app" => "foo",
                "controller" => "bar",
                "action" => "baz",
                "name" => null,
                "cacheTtl" => null,
                "requestMethods" => array(),
            ),
            "headers"      => array(
                "Content-Type" => "text/html",
            ),
            "ifNoneMatch"  => null,
        ), $this->response->toArray());
    }

    public function testSetFromArrayWithNoPath() {
        $this->response->setFromArray(array(
            "body" => "test",
            "isRedirect" => false,
            "responseCode" => 404,
            "path" => null,
            "headers" => array(),
        ));

        $this->assertEquals("test", $this->response->getBody());
        $this->assertFalse($this->response->isRedirect());
        $this->assertEquals(404, $this->response->getResponseCode());
        $this->assertEquals(null, $this->response->getPath());
    }

    public function testSetFromArrayWithPathData() {
        $this->response->setFromArray(array(
            "body" => "test",
            "isRedirect" => false,
            "responseCode" => 404,
            "path" => array(
                "app" => "foo",
                "controller" => "bar",
                "action" => "baz",
            ),
            "headers" => array(),
        ));

        $path = $this->response->getPath();

        $this->assertEquals("test", $this->response->getBody());
        $this->assertFalse($this->response->isRedirect());
        $this->assertEquals(404, $this->response->getResponseCode());

        $this->assertEquals("foo", $path->getApp());
        $this->assertEquals("bar", $path->getController());
        $this->assertEquals("baz", $path->getAction());
    }
}
