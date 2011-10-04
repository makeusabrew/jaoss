<?php
class PathManagerTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        PathManager::reset();
    }

    public function tearDown() {
        JaossRequest::destroyInstance();
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
        $this->assertFalse($path->isCacheable());
    }

    public function testMatchUrlWithSimplePathsLoadedAndWithMatch() {
		PathManager::loadPath("/foo", "bar", "baz", "test");
        $path = PathManager::matchUrl("/foo");
        $this->assertEquals("^/foo$", $path->getPattern());
        $this->assertEquals("bar", $path->getAction());
        $this->assertEquals("baz", $path->getController());
        $this->assertEquals("apps/test", $path->getLocation());
        $this->assertFalse($path->isCacheable());
    }

    public function testMatchUrlWithSimpleAssociativeLoadPathsMethod() {
        PathManager::loadPaths(array(
            "pattern" => "/foo",
            "action" => "bar",
            "controller" => "baz",
            "location" => "test",
        ));
        $path = PathManager::matchUrl("/foo");
        $this->assertEquals("^/foo$", $path->getPattern());
        $this->assertEquals("bar", $path->getAction());
        $this->assertEquals("baz", $path->getController());
        $this->assertEquals("apps/test", $path->getLocation());
        $this->assertFalse($path->isCacheable());
    }

    public function testMatchUrlFailsWithIncorretRequestMethod() {
        $request = JaossRequest::getInstance();
        PathManager::loadPath("/foo", "bar", "baz", "test", "POST");
        try {
            $path = PathManager::matchUrl("/foo");
        } catch (CoreException $e) {
            $this->assertEquals(CoreException::URL_NOT_FOUND, $e->getCode());
            return;
        }
        $this->fail("expected exception not raised");
    }

    public function testMatchUrlFailsWithIncorretRequestMethodLowerCase() {
        $request = JaossRequest::getInstance();
        PathManager::loadPath("/foo", "bar", "baz", "test", "post");
        try {
            $path = PathManager::matchUrl("/foo");
        } catch (CoreException $e) {
            $this->assertEquals(CoreException::URL_NOT_FOUND, $e->getCode());
            return;
        }
        $this->fail("expected exception not raised");
    }

    public function testMatchUrlFailsWhenExpectedMethodIsGetButMethodIsPost() {
        $request = JaossRequest::getInstance();
        $request->setMethod("POST");
        PathManager::loadPath("/foo", "bar", "baz", "test", "GET");
        try {
            $path = PathManager::matchUrl("/foo");
        } catch (CoreException $e) {
            $this->assertEquals(CoreException::URL_NOT_FOUND, $e->getCode());
            return;
        }
        $this->fail("expected exception not raised");
    }

    public function testMatchUrlSucceedsWhenExpectedMethodIsArrayContainingGetAndPost() {
        $request = JaossRequest::getInstance();
        PathManager::loadPath("/foo", "bar", "baz", "test", array("GET", "POST"));
        $path = PathManager::matchUrl("/foo");
        $this->assertEquals(array("GET", "POST"), $path->getRequestMethods());
    }

    public function testMatchUrlSucceedsWhenExpectedMethodIsAll() {
        $request = JaossRequest::getInstance();
        $request->setMethod("POST");

        PathManager::loadPath("/foo", "bar", "baz", "test", "all");
        $path = PathManager::matchUrl("/foo");
        $this->assertEquals(array("ALL"), $path->getRequestMethods());
    }

    public function testMatchUrlSucceedsWhenExpectedMethodSetByLoadPathsAssociative() {
        $request = JaossRequest::getInstance();
        $request->setMethod("POST");

        PathManager::loadPaths(
            array(
                "pattern" => "/foo",
                "action" => "foo",
                "controller" => "foo",
                "location" => "foo",
                "method" => "post",
            )
        );
        $path = PathManager::matchUrl("/foo");
        $this->assertEquals(array("POST"), $path->getRequestMethods());
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
            $this->assertEquals(11, $e->getCode());
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
            $this->assertEquals(11, $e->getCode());
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

    public function testMatchUrlIgnoresDiscardedPaths() {
        PathManager::loadPath("/foo", "index", "Foo", "FooApp");
        PathManager::loadPath("/bar", "bar", "Foo", "FooApp");

        try {
            PathManager::matchUrl("/nomatch");
        } catch (CoreException $e) {
            $this->assertEquals(CoreException::URL_NOT_FOUND, $e->getCode());
            // this isn't a valid use case for discarded paths, but it's a valid simulation
            // in reality, a path is only discarded by the request object when a
            // particular exception is thrown, but to simulate the equivalent, let's just match
            // a *valid* path and make sure it doesn't work
            try {
                PathManager::matchUrl("/foo");
            } catch (CoreException $e) {
                $this->assertEquals(CoreException::URL_NOT_FOUND, $e->getCode());
                return;
            }
        }
        $this->fail("Expected exception not raised");
    }

    public function testReloadPathsMarksPathsNotDiscarded() {
        PathManager::loadPath("/foo", "index", "Foo", "FooApp");

        try {
            PathManager::matchUrl("/nomatch");
        } catch (CoreException $e) {
            $this->assertEquals(CoreException::URL_NOT_FOUND, $e->getCode());
            list($path) = PathManager::getPaths();
            $this->assertTrue($path->isDiscarded());
            PathManager::reloadPaths();
            $path = PathManager::matchUrl("/foo");
            $this->assertFalse($path->isDiscarded());
        }
    }
}
