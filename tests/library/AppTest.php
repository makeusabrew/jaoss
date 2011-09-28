<?php
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

    public function testNewAppTitleIsEmptyString() {
        $this->assertEquals("", $this->app->getTitle());
    }

    public function testNewAppWithFolderPassedToConstructor() {
        $this->app = new App("myapp");
        $this->assertFalse($this->app->getLoaded());
        $this->assertEquals("myapp", $this->app->getFolder());
        $this->assertEquals("Myapp", $this->app->getTitle());
    }
	
	public function testGetAndSetFolder() {
		$this->assertEquals(NULL, $this->app->getFolder());
		$this->app->setFolder("foo");
		$this->assertEquals("foo", $this->app->getFolder());
		$this->app->setFolder("bar");
		$this->assertEquals("bar", $this->app->getFolder());
	}

    public function testSetLoaded() {
        $this->app->setLoaded(true);
        $this->assertTrue($this->app->getLoaded());
    }

    public function testLoadPathsFailsWhenNoPathsFileFound() {
        $this->assertFalse($this->app->loadPaths());
    }

    public function testLoadPathsSucceedsWithValidPathsFileFound() {
        $this->app->setPathsFile(JAOSS_ROOT."tests/fixtures/apps/test/paths.php");
        $this->assertTrue($this->app->loadPaths());
    }
}
