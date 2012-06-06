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
}
