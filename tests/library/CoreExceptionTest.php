<?php
class CoreExceptionTest extends PHPUnit_Framework_TestCase {
	public function testGetArg() {
		$e = new CoreException("Fake Exception", 0, array(
			"arg" => "foo",
			"another" => "bar",
		));
		
		$this->assertEquals("foo", $e->getArg("arg"));
		$this->assertEquals("bar", $e->getArg("another"));
		$this->assertEquals("<strong>foo</strong>", $e->ga("arg"));
		$this->assertEquals(null, $e->getArg("bad index"));
	}

    /**
     * @dataProvider provider
     */
    public function testGetDefaultResponseCode($errorCode, $statusCode) {
        $e = new CoreException("Foo", $errorCode);

        $this->assertEquals($statusCode, $e->getDefaultResponseCode());
    }

    public function provider() {
        return array(
            array(0, 500),
            array(1, 404),
            array(2, 404),
            array(3, 404),
            array(4, 500),
            array(5, 500),
            array(6, 500),
            array(7, 404),
            array(8, 404),
            array(9, 500),
            array(10, 500),
            array(11, 404),
            array(12, 404),
            array(13, 500),
            array(14, 404),
            array(15, 500),
            array(16, 404),
            array(17, 500),
            array(18, 500),
            array(19, 500),
        );
    }
}
