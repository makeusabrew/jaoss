<?php
require_once 'PHPUnit/Framework.php';

if (!defined("BASE")) {
	define("BASE", __DIR__."/../");
}

require(BASE."app_manager.php");

class AppManagerTest extends PHPUnit_Framework_TestCase {
	public function testInstalledAppsStartsEmptyAndIsArray() {
		$this->assertType("array", AppManager::getInstalledApps());
		$this->assertEquals(0, count(AppManager::getInstalledApps()));
	}
	
	public function testGetAppReturnsCorrectValue() {
		$this->assertFalse(AppManager::isAppInstalled("Fake App"));
	}
}
