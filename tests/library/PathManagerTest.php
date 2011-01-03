<?php
class PathManagerTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        PathManager::reset();
    }

	public function testPathsStartsEmptyAndIsArray() {
		$paths = PathManager::getPaths();
		$this->assertInternalType("array", $paths);
		$this->assertEquals(0, count($paths));
	}
	
	public function testPathsCountIsZeroAfterPathReset() {
		$paths = PathManager::getPaths();
		$this->assertInternalType("array", $paths);
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
        try {
            PathManager::loadPaths("foo", "bar", "baz", "test");	// loadPaths expects arrays
        } catch (CoreException $e) {
            $this->assertEquals(0, $e->getCode());  //@todo change when this exception gets a code!
            return;
        }
        $this->fail("Expected exception not raised");
	}
	
	public function testAddPathsThrowsExceptionWithEmptyArrayArgument() {
        try {
            PathManager::loadPaths(array());
        } catch (CoreException $e) {
            $this->assertEquals(0, $e->getCode());  //@todo change when this exception gets a code!
            return;
        }
        $this->fail("Expected exception not raised");
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

    public function testMatchUrlWithSimplePathsLoadedAndWithMatch() {
		PathManager::loadPath("/foo", "bar", "baz", "test");
        $path = PathManager::matchUrl("/foo");
        $this->assertEquals("^/foo$", $path->getPattern());
        $this->assertEquals("bar", $path->getAction());
        $this->assertEquals("baz", $path->getController());
        $this->assertEquals("apps/test", $path->getLocation());
    }

    public function testsetPrefix() {
        PathManager::setPrefix("/someprefix");
        PathManager::loadPath("/foo", "bar", "baz", "test");
        $path = PathManager::matchUrl("/someprefix/foo");
        $this->assertEquals("^/someprefix/foo$", $path->getPattern());

        try {
            PathManager::matchUrl("/foo");
        } catch (CoreException $e) {
            $this->assertEquals(CoreException::URL_NOT_FOUND, $e->getCode());
            return;
        }
        $this->fail("expected exception not raised");
    }

    public function testClearPrefix() {
        PathManager::setPrefix("/someprefix");
        PathManager::loadPath("/foo", "bar", "baz", "test");
        $path = PathManager::matchUrl("/someprefix/foo");
        $this->assertEquals("^/someprefix/foo$", $path->getPattern());
        
        PathManager::clearPrefix();
        PathManager::loadPath("/foo", "bar", "baz", "test");
        $path = PathManager::matchUrl("/foo");
        $this->assertEquals("^/foo$", $path->getPattern());
    }
    
    public function testGetPathForEmptyOptionsArray() {
        $options = array();

        try {
            $path = PathManager::getPathForOptions($options);
        } catch (CoreException $e) {
            $this->assertEquals(0, $e->getCode());
            return;
        }
        $this->fail("Expected Exception Not Raised");
    }

    public function testGetPathForOptionsWithNoMatch() {
        $options = array(
            "controller" => "Foo",
            "app" => "Foo",
            "action" => "index",
        );

        try {
            $path = PathManager::getPathForOptions($options);
        } catch (CoreException $e) {
            $this->assertEquals(0, $e->getCode());
            return;
        }
        $this->fail("Expected Exception Not Raised");
    }

    public function testGetPathForOptionsWithMatch() {
        $options = array(
            "controller" => "Foo",
            "app" => "FooApp",
            "action" => "index",
        );

        PathManager::loadPath("/foo", "index", "Foo", "FooApp");

        $path = PathManager::getPathForOptions($options);

        $this->assertEquals("/foo", $path->getPattern());
        $this->assertEquals("index", $path->getAction());
        $this->assertEquals("Foo", $path->getController());
        $this->assertEquals("FooApp", $path->getApp());
    }

    public function testGetUrlForOptionsWithArgumentAndMatch() {
        $options = array(
            "controller" => "Foo",
            "app" => "FooApp",
            "action" => "index",
            "id" => "42",
        );

        PathManager::loadPath("/foo/(?P<id>\d+)", "index", "Foo", "FooApp");

        $url = PathManager::getUrlForOptions($options);
        
        $this->assertEquals("/foo/42", $url);
    }

    public function testGetUrlForOptionsWithArgumentAndNoMatch() {
        $options = array(
            "controller" => "Foo",
            "app" => "FooApp",
            "action" => "index",
            "notFound" => "1234",
        );

        PathManager::loadPath("/foo/(?P<id>\d+)", "index", "Foo", "FooApp");

        try {
            $url = PathManager::getUrlForOptions($options);
        } catch (CoreException $e) {
            $this->assertEquals(0, $e->getCode());
            return;
        }
        $this->fail("Expected exception not raised");
    }
}
