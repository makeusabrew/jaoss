<?php
class InitExceptionTest extends PHPUnit_Framework_TestCase {
    public function testDefaultMessage() {
        $e = new InitException(null);

        $this->assertEquals("Init Exception", $e->getMessage());
    }

    public function testDefaultCode() {
        $e = new InitException(null);

        $this->assertEquals(0, $e->getCode());
    }

    public function testCustomParameters() {
        $e = new InitException(null, "Test", 3);

        $this->assertEquals("Test", $e->getMessage());
        $this->assertEquals(3, $e->getCode());
    }

    public function testGetResponse() {
        $response = new stdClass();
        $e = new InitException($response);

        $this->assertSame($response, $e->getResponse());
    }
}
