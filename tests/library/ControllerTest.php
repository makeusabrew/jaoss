<?php
class ControllerTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->stub = new ConcreteController();
    }

    public function testGetMatch() {
        $path = new Path();
        $path->setMatches(array());
        $this->stub->setPath($path);

        $this->assertNull($this->stub->getMatch("fooBar"));
    }

}

// I'd much rather use a mock here but for some reason code coverage doesn't
// seem to work properly. Look at fixing this later.
class ConcreteController extends Controller {
    public function __construct() {
        // don't want abstract controller construct firing cos it throws an exception
        // could look at moving stuff out of construct?
    }
}
