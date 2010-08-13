<?php
require_once("core_exception.php");
require_once("log.php");
require_once("settings.php");

class CoreExceptionTest extends PHPUnit_Framework_TestCase {
	public function testGetArg() {
		/* disabled until we work around log issues 
		$e = new CoreException("Fake Exception", 0, array(
			"arg" => "foo",
			"another" => "bar",
		));
		
		$this->assertEquals("foo", $e->getArg("foo"));
		$this->assertEquals("bar", $this->getArg("another"));
		$this->assertEquals(NULL, $this->getArg("bad index"));
		*/
	}
}
