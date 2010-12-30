<?php
class ErrorHandlerTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->eh = new ErrorHandler();
    }

    public function tearDown() {
        unset($this->eh);
    }

    public function testErrorCodeZeroWithVerboseErrors() {
        $e = new CoreException("Some Default Exception");
        $this->eh->handleError($e);
        $this->assertEquals(404, $this->eh->getResponse()->getResponseCode(), "Correct response code");
        $this->assertTrue(strpos($this->eh->getResponse()->getBody(), "Some Default Exception") !== false, "Body has correct contents");
    }

    public function testErrorCodeZeroPDOExceptionWithVerboseErrorsTriggersUnknownTemplate() {
        $e = new PDOException("Some Error");
        $this->eh->handleError($e);
        $this->assertEquals(500, $this->eh->getResponse()->getResponseCode(), "Correct response code");
        $this->assertTrue(strpos($this->eh->getResponse()->getBody(), "Unknown Code / Path") !== false, "Body has correct contents");
    }

    public function testErrorCodeZeroSmartyCompilerException() {
        $e = new SmartyCompilerException("Some Error");
        $this->eh->handleError($e);
        $this->assertEquals(500, $this->eh->getResponse()->getResponseCode(), "Correct response code");
        $this->assertTrue(strpos($this->eh->getResponse()->getBody(), "An error occured whilst processing the smarty template") !== false, "Body has correct contents");
    }

    public function testUnhandledExceptionType() {
        $e = new Exception("Some Error");
        $this->eh->handleError($e);
        $this->assertEquals(500, $this->eh->getResponse()->getResponseCode(), "Correct response code");
        $this->assertTrue(strpos($this->eh->getResponse()->getBody(), "Unknown Exception") !== false, "Body has correct contents");
    }
}
