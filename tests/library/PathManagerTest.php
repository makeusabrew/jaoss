<?php
class PathManagerTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        PathManager::reset();
    }

	public function testPathsStartsEmptyAndIsArray() {
		$paths = PathManager::getPaths();
		$this->assertType("array", $paths);
		$this->assertEquals(0, count($paths));
	}
	
	public function testPathsCountIsZeroAfterPathReset() {
		$paths = PathManager::getPaths();
		$this->assertType("array", $paths);
		$this->assertEquals(0, count($paths));
	}
	
	public function testPathsCountIsOneAfterPathAddedViaLoadPath() {
		$paths = PathManager::getPaths();
		$this->assertEquals(0, count($paths));
		
		PathManager::loadPath("foo", "bar", "baz", "test");
		
		$paths = PathManager::getPaths();
		$this->assertEquals(1, count($paths));
	}
	
	public function testAddPathsThrowsExceptionWithStringArgument() {
		$this->setExpectedException("CoreException");
		PathManager::loadPaths("foo", "bar", "baz", "test");	// loadPaths expects arrays
	}
	
	public function testAddPathsThrowsExceptionWithEmptyArrayArgument() {
		$this->setExpectedException("CoreException");
		PathManager::loadPaths(array());
	}
	
	public function testPathsCountIsOneAfterPathAddedViaLoadPaths() {
		$paths = PathManager::getPaths();
		$this->assertEquals(0, count($paths));
		
		PathManager::loadPaths(array("foo", "bar", "baz", "test"));
		
		$paths = PathManager::getPaths();
		$this->assertEquals(1, count($paths));
	}

    public function testMatchUrlWithNoPathsLoaded() {
        $this->setExpectedException("CoreException");
        PathManager::matchUrl("/");
    }

    public function testMatchUrlWithPathsLoadedButNoMatch() {
		PathManager::loadPath("foo", "bar", "baz", "test");
        try {
            PathManager::matchUrl("/bad/url");
        } catch (CoreException $e) {
            $this->assertEquals(CoreException::URL_NOT_FOUND, $e->getCode());
            return;
        }
        $this->fail("Exception not raised");
    }

    public function testMatchUrlWithPathsLoadedAndWithMatch() {
		PathManager::loadPath("^/foo$", "bar", "baz", "test");
        $path = PathManager::matchUrl("/foo");
        $this->assertEquals("^/foo$", $path->getPattern());
        $this->assertEquals("bar", $path->getAction());
        $this->assertEquals("baz", $path->getController());
        $this->assertEquals("apps/test", $path->getLocation());
    }
}
