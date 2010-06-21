<?php
require_once 'PHPUnit/Framework.php';

if (!defined("BASE")) {
	define("BASE", __DIR__."/../");
}
require(BASE."core_exception.php");
require(BASE."path.php");
require(BASE."path_manager.php");

class PathManagerTest extends PHPUnit_Framework_TestCase {

	public function testPathsStartsEmptyAndIsArray() {
		$paths = PathManager::getPaths();
		$this->assertType("array", $paths);
		$this->assertEquals(0, count($paths));
	}
	
	public function testPathsCountIsZeroAfterPathReset() {
		PathManager::resetPaths();
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
		$this->setExpectedException("CoreException");
		PathManager::resetPaths();
		$paths = PathManager::getPaths();
		$this->assertEquals(0, count($paths));
		
		PathManager::loadPath(array("foo", "bar", "baz", "test"));
		
		$paths = PathManager::getPaths();
		$this->assertEquals(1, count($paths));
	}
}
