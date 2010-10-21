<?php
class ControllerTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        // we have to mock here, 
        // 1) because Controller is abstract
        // 2) because we need to explicitly disable the controller's __construct (which throws exceptions etc)
        // maybe look at controller and taking most of the initialisation outside __construct() so we can ,ock it properly
        $this->stub = $this->getMock("Controller", array(), array(), "MockController", false);
    }

    public function testGetMatch() {
        $path = new Path();
        $path->setMatches(array());
        $this->stub->setPath($path);

        $this->assertNull($this->stub->getMatch("fooBar"));
    }

}
