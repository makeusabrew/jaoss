<?php
require_once 'PHPUnit/Framework.php';

if (!defined("BASE")) {
	define("BASE", __DIR__."/../");
}

require(BASE."session.php");

class SessionTest extends PHPUnit_Framework_TestCase {
    private $session = NULL;

    protected function setUp() {
        $this->session = Session::getInstance("foo_bar_baz_xa12gf");
    }

    protected function tearDown() {
        $this->session->destroy();
    }

    public function testSetAndGetWithStrings() {
        $this->session->foo = "bar";
        $this->assertEquals("bar", $this->session->foo);
    }

    public function testGetWithInvalidVariable() {
        $this->assertNull($this->session->foo);
    }

    public function testSetOverridesAlreadySetVariable() {
        $this->session->foo = "bar";
        $this->assertEquals("bar", $this->session->foo);
        $this->session->foo = "baz";
        $this->assertEquals("baz", $this->session->foo);
    }

    public function testSetAndGetWithArrays() {
        $this->session->foo = array(10, 20, 30);
        $this->assertEquals(array(10, 20, 30), $this->session->foo);
    }

    public function testSetAndGetWithObjects() {
        $o = new stdClass();
        $o->var = "val";
        $o->foo = "bar";

        $this->session->foo = $o;

        $this->assertEquals($o, $this->session->foo);
    }
    
    public function testUnset() {
    	$this->session->foo = "bar";
    	$this->assertEquals("bar", $this->session->foo);
    	unset($this->session->foo);
    	$this->assertNull($this->session->foo);
    }
}
