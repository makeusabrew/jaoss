<?php
class ControllerTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->stub = new ConcreteController();
    }

    public function testGetMatch() {
        $path = new JaossPath();
        $path->setMatches(array());
        $this->stub->setPath($path);

        $this->assertNull($this->stub->getMatch("fooBar"));
    }

    public function testAssignMethodThrowsExceptionWhenAssigningAlreadySetVariable() {
        $this->stub->assign("foo", "bar");
        $this->stub->assign("baz", "test");

        // now assert that an exception is thrown
        try {
            $this->stub->assign("foo", "newValue");
        } catch (CoreException $e) {
            $this->assertEquals(9, $e->getCode());
            $this->assertEquals("Variable already assigned", $e->getMessage());
            return;
        }
        $this->fail("Expected exception not raised");
    }

    public function testAssignMethodAllowsVariableToBeSetAfterUnsetting() {
        try {
            $this->stub->assign("foo", "bar");
            $this->stub->unassign("foo");
            $this->stub->assign("foo", "baz");
        } catch (CoreException $e) {
            $this->fail("Exception should not be thrown");
        }
    }

    public function testIsAssignedMethod() {
        $this->assertFalse($this->stub->isAssigned("foo"));
        $this->stub->assign("foo", "bar");
        $this->assertTrue($this->stub->isAssigned("foo"));
        $this->stub->unassign("foo");
        $this->assertFalse($this->stub->isAssigned("foo"));
    }

    public function testRenderJsonAssignsMsgVariableToOkIfNotSet() {
        $this->stub->renderJson();

        $data = json_decode(
            $this->stub->getResponse()->getBody()
        );
        $this->assertEquals('OK', $data->msg);
    }

    public function testRenderJsonDoesNotAssignMsgVariableIfSet() {
        $this->stub->assign('msg', 'ERROR');
        $this->stub->renderJson();

        $data = json_decode(
            $this->stub->getResponse()->getBody()
        );
        $this->assertEquals('ERROR', $data->msg);
    }
}

// I'd much rather use a mock here but for some reason code coverage doesn't
// seem to work properly. Look at fixing this later.
class ConcreteController extends Controller {
    public function __construct() {
        // don't want abstract controller construct firing cos it throws an exception
        // could look at moving stuff out of construct?
        $this->response = new JaossResponse();
    }
}
