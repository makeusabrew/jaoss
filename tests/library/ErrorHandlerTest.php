<?php
class ErrorHandlerTest extends PHPUnit_Framework_TestCase {
    protected function assertResponseCode($expected) {
        $this->assertEquals($expected, $this->eh->getResponse()->getResponseCode(), "Correct response code");
    }

    protected function assertBodyHasContents($expected) {
        $this->assertTrue(strpos($this->eh->getResponse()->getBody(), $expected) !== false, "Body has correct contents");
    }

    public function setUp() {
        $this->eh = new ErrorHandler();
    }

    public function tearDown() {
        unset($this->eh);
    }

    public function testErrorCodeZeroWithVerboseErrors() {
        $e = new CoreException("Some Default Exception");
        $this->eh->handleError($e);
        $this->assertEquals(500, $this->eh->getResponse()->getResponseCode(), "Correct response code");
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

    public function testUrlNotFoundExceptionHandling() {
        $e = new CoreException(
			"No matching path for URL /foo/bar/baz",
			CoreException::URL_NOT_FOUND,
			array(
				"paths" => array(),
				"url" => "/foo/bar/baz",
			)
		);
        $this->eh->handleError($e);
        $this->assertResponseCode(404);
        $this->assertBodyHasContents("No matching path for URL /foo/bar/baz");
        $this->assertBodyHasContents("URL <strong>/foo/bar/baz</strong> not found");
    }

    public function testCouldNotAttachCookieJarStorageExceptionHandling() {
        $e = new CoreException(
            "Could not attach cookie jar storage",
            CoreException::COULD_NOT_ATTACH_COOKIE_JAR,
            array(
                "class" => "DefaultCookieJarStorage",
            )
        );
        $this->eh->handleError($e);
        $this->assertResponseCode(500);
        $this->assertBodyHasContents("Could not attach cookie jar storage");
        $this->assertBodyHasContents("The class <strong>DefaultCookieJarStorage</strong> could not be found");
    }

    public function testSettingNotFoundExceptionHandling() {
        $e = new CoreException(
            "Setting 'foo.bar' not found",
            CoreException::SETTING_NOT_FOUND,
            array(
                "mode" => "test",
                "setting_key" => "foo.bar",
            )
        );
        $this->eh->handleError($e);
        $this->assertResponseCode(404);
        $this->assertBodyHasContents("The setting <strong>foo.bar</strong> could not be found for mode: <strong>test</strong>");
    }
}
