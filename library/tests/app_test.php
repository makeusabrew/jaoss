<?php
require_once 'PHPUnit/Framework.php';

if (!defined("BASE")) {
	define("BASE", __DIR__."/../");
}

require(BASE."app.php");

class AppTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		$this->app = new App();
	}
	
	protected function tearDown() {
		unset($this->app);
	}
	
	public function testNewAppIsNotLoaded() {
		$this->assertFalse($this->app->getLoaded());
	}
	
	public function testNewAppFolderIsNull() {
		$this->assertNull($this->app->getFolder());
	}
	
	public function testGetAndSetFolder() {
		$this->assertEquals(NULL, $this->app->getFolder());
		$this->app->setFolder("foo");
		$this->assertEquals("foo", $this->app->getFolder());
		$this->app->setFolder("bar");
		$this->assertEquals("bar", $this->app->getFolder());
	}
}
