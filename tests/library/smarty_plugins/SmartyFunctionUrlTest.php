<?php
require_once(JAOSS_ROOT."library/smarty_plugins/function.url.php");

class SmartyFunctionUrlTest extends PHPUnit_Framework_TestCase {

    public function testNoNameThrowsException() {
        $params = array();
        try {
            smarty_function_url($params, null);
        } catch (ErrorException $e) {
            $this->assertEquals("No path name specified", $e->getMessage());
            return;
        }
        $this->fail("Expected exception not raised");
    }

    public function testNameForInvalidPathThrowsException() {
        $params = array(
            "path" => "invalid",
        );

        try {
            smarty_function_url($params, null);
        } catch (CoreException $e) {
            $this->assertEquals(CoreException::PATH_NAME_NOT_FOUND, $e->getCode());
            return;
        }
        $this->fail("Expected exception not raised");
    }

    public function testNameForValidPathReturnsUrl() {
        PathManager::loadPath("/foo/bar", "index", "Foo", "FooApp", null, null, "my_path");

        $params = array(
            "path" => "my_path",
        );

        $this->assertEquals("/foo/bar", smarty_function_url($params, null));
    }

    public function testValidNameReturnsCorrectUrlInSubfolderMode() {
        $this->request = JaossRequest::getInstance();
        $this->request->setProperties(array(
            "folder_base" => "/sub/folder/"
        ));

        PathManager::loadPath("/my/path", "index", "Foo", "FooApp", null, null, "new_path");

        $params = array(
            "path" => "new_path",
        );

        $this->assertEquals("/sub/folder/my/path", smarty_function_url($params, null));
    }
}
