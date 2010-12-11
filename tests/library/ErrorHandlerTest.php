<?php
class ErrorHandlerrTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->eh = new ErrorHandler();
    }

    public function tearDown() {
        unset($this->eh);
    }

    public function testErrorCodeZeroWithVerboseErrors() {
        $e = new CoreException("Some Default Exception");
        $this->eh->handleError($e);
        $this->assertEquals(404, $this->eh->getResponse()->getResponseCode());
        $this->assertTrue(strpos($this->eh->getResponse()->getBody(), "Some Default Exception") !== false);
    }
}
