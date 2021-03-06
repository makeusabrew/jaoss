<?php
class AppManagerTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        AppManager::reset();
    }

	public function testInstalledAppsStartsEmptyAndIsArray() {
		$this->assertInternalType("array", AppManager::getInstalledApps());
		$this->assertEquals(0, count(AppManager::getInstalledApps()));
	}
	
	public function testGetAppReturnsCorrectValue() {
		$this->assertFalse(AppManager::isAppInstalled("Fake App"));
	}

    public function testInstallApp() {
        if (!is_dir(PROJECT_ROOT."apps")) {
            mkdir(PROJECT_ROOT."apps");
            $appDirCreated = true;
        }
        $this->createDir(PROJECT_ROOT."apps/testapp");
        AppManager::installApp("testapp");
        $this->removeDir(PROJECT_ROOT."apps/testapp");
        if (isset($appDirCreated)) {
            $this->removeDir(PROJECT_ROOT."apps/");
        }

        $this->assertEquals(1, count(AppManager::getInstalledApps()));
    }

    public function testGetInstalledAppPaths() {
        if (!is_dir(PROJECT_ROOT."apps")) {
            mkdir(PROJECT_ROOT."apps");
            $appDirCreated = true;
        }
        $this->createDir(PROJECT_ROOT."apps/testapp");
        AppManager::installApp("testapp");
        $this->removeDir(PROJECT_ROOT."apps/testapp");
        if (isset($appDirCreated)) {
            $this->removeDir(PROJECT_ROOT."apps/");
        }
        
        $this->assertEquals(array("testapp"), AppManager::getAppPaths());
    }

    public function testGetInstalledAppsHash() {
        if (!is_dir(PROJECT_ROOT."apps")) {
            mkdir(PROJECT_ROOT."apps");
            $appDirCreated = true;
        }
        $this->createDir(PROJECT_ROOT."apps/testapp");

        AppManager::installApp("testapp");

        $this->removeDir(PROJECT_ROOT."apps/testapp");
        if (isset($appDirCreated)) {
            $this->removeDir(PROJECT_ROOT."apps/");
        }
        
        $this->assertEquals(sha1("testapp"), AppManager::getInstalledAppsHash());
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
