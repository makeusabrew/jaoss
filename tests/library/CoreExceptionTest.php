<?php
class CoreExceptionTest extends PHPUnit_Framework_TestCase {
	public function testGetArg() {
		$e = new CoreException("Fake Exception", 0, array(
			"arg" => "foo",
			"another" => "bar",
		));
		
		$this->assertEquals("foo", $e->getArg("arg"));
		$this->assertEquals("bar", $e->getArg("another"));
		$this->assertEquals(NULL, $e->getArg("bad index"));
	}
}
