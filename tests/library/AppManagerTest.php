<?php
class AppManagerTest extends PHPUnit_Framework_TestCase {
	public function testInstalledAppsStartsEmptyAndIsArray() {
		$this->assertType("array", AppManager::getInstalledApps());
		$this->assertEquals(0, count(AppManager::getInstalledApps()));
	}
	
	public function testGetAppReturnsCorrectValue() {
		$this->assertFalse(AppManager::isAppInstalled("Fake App"));
	}
}
