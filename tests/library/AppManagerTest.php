<?php
class AppManagerTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        AppManager::reset();
    }

	public function testInstalledAppsStartsEmptyAndIsArray() {
		$this->assertType("array", AppManager::getInstalledApps());
		$this->assertEquals(0, count(AppManager::getInstalledApps()));
	}
	
	public function testGetAppReturnsCorrectValue() {
		$this->assertFalse(AppManager::isAppInstalled("Fake App"));
	}

    public function testLoadAppFromPath() {
        $this->createDir(PROJECT_ROOT."apps");
        $this->createDir(PROJECT_ROOT."apps/testapp");
        AppManager::loadAppFromPath("testapp");
        $this->removeDir(PROJECT_ROOT."apps/testapp");
        $this->removeDir(PROJECT_ROOT."apps/");

        $this->assertEquals(1, count(AppManager::getInstalledApps()));
    }

    public function testGetInstalledAppPaths() {
        $this->createDir(PROJECT_ROOT."apps");
        $this->createDir(PROJECT_ROOT."apps/testapp");
        AppManager::loadAppFromPath("testapp");
        $this->removeDir(PROJECT_ROOT."apps/testapp");
        $this->removeDir(PROJECT_ROOT."apps/");
        
        $this->assertEquals(array("testapp"), AppManager::getAppPaths());
    }

    protected function createDir($dir) {
        if (!is_dir($dir)) {
            mkdir($dir);
        }
    }

    protected function removeDir($dir) {
        if (is_dir($dir)) {
            rmdir($dir);
        }
    }
}
