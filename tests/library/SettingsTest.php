<?php
class SettingsTest extends PHPUnit_Framework_TestCase {

    protected function tearDown() {
        // reset was nerfing everything after this test which
        // relied on settings!
        //Settings::reset();
    }

    public function testInvalidModeThrowsException() {
        try {
            Settings::setMode("invalid");
        } catch (CoreException $e) {
            $this->assertEquals("Mode is not supported", $e->getMessage());
            $this->assertEquals(CoreException::INVALID_MODE, $e->getCode());
            return;
        }

        $this->fail("Expected exception not raised");
    }

    public function testGetInvalidSettingThrowsExpectedException() {
        try {
            Settings::getValue("foo.bar");
        } catch (CoreException $e) {
            $this->assertEquals("Setting 'foo.bar' not found", $e->getMessage());
            $this->assertEquals(CoreException::SETTING_NOT_FOUND, $e->getCode());
            return;
        }
        $this->fail("Expected exception not raised");
    }

    public function testSetFromArray() {
        $original = Settings::getSettings();
        Settings::setFromArray(array(
            'foo' => array(
                'bar' => 'baz',
            ),
        ));

        $this->assertEquals(array('bar' => 'baz'), Settings::getSettings('foo'));

        Settings::setFromArray($original);
    }
}
