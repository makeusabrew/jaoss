<?php
class ControllerTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->stub = new ConcreteController();
    }

    public function testGetMatchReturnsNullWithNoMatch() {
        $path = new JaossPath();
        $path->setMatches(array());
        $this->stub->setPath($path);

        $this->assertNull($this->stub->getMatch("fooBar"));
    }

    public function testGetMatchReturnsDefaultValueIfSuppliedAndNoMatch() {
        $path = new JaossPath();
        $path->setMatches(array());
        $this->stub->setPath($path);

        $this->assertEquals("123", $this->stub->getMatch("fooBar", "123"));
        $this->assertTrue($this->stub->getMatch("fooBar", true));
    }

    public function testGetMatchReturnsValueWithMatch() {
        $path = new JaossPath();
        $path->setMatches(array(
            "foo" => "bar",
            "baz" => 321
        ));
        $this->stub->setPath($path);

        $this->assertEquals("bar", $this->stub->getMatch("foo"));
        $this->assertEquals(321, $this->stub->getMatch("baz"));
    }

    public function testGetMatchReturnsValueWithMatchEvenIfDefaultArgumentSupplied() {
        $path = new JaossPath();
        $path->setMatches(array(
            "foo" => "bar",
            "baz" => 321
        ));
        $this->stub->setPath($path);

        $this->assertEquals("bar", $this->stub->getMatch("foo", "default"));
        $this->assertEquals(321, $this->stub->getMatch("baz", true));
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

    public function testFactoryThrowsExceptionWhenNoClassFound() {
        try {
            Controller::factory("Unknown");
        } catch (CoreException $e) {
            $this->assertEquals("Could not find controller in any path", $e->getMessage());
            $this->assertEquals(CoreException::CONTROLLER_CLASS_NOT_FOUND, $e->getCode());
            return;
        }
        $this->fail("Expected exception not found");
    }

    public function testRedirectWithString() {
        $this->assertTrue($this->stub->redirect("/"));
        $this->assertEquals(303, $this->stub->getResponse()->getResponseCode());
        $this->assertEquals("/", $this->stub->getResponse()->getRedirectUrl());
    }

    public function testRedirectWithValidArrayOptions() {
        // bah, we can't mock static methods, so this gets a bit long winded
        PathManager::reset();
        PathManager::loadPath("/test", "test", "Test", "test");
        list($path) = PathManager::getPaths();
        $this->stub->setPath($path);

        $this->assertTrue($this->stub->redirect(array(
            "action" => "test",
        )));

        $this->assertEquals(303, $this->stub->getResponse()->getResponseCode());
        $this->assertEquals("/test", $this->stub->getResponse()->getRedirectUrl());
    }

    public function testRedirectWithInvalidArrayOptions() {
        PathManager::reset();
        PathManager::loadPath("/test", "test", "Test", "test");
        list($path) = PathManager::getPaths();
        $this->stub->setPath($path);

        try {
            $this->assertTrue($this->stub->redirect(array(
                "action" => "invalid",
            )));
        } catch (CoreException $e) {
            $this->assertEquals("No Path found for options", $e->getMessage());
            $this->assertEquals(CoreException::NO_PATH_FOUND_FOR_OPTIONS, $e->getCode());
            return;
        }
        $this->fail("Expected exception not raised");
    }

    public function testRedirectActionWithValidValue() {
        PathManager::reset();
        PathManager::loadPath("/test", "test", "Test", "test");
        list($path) = PathManager::getPaths();
        $this->stub->setPath($path);

        $this->assertTrue($this->stub->redirectAction("test"));

        $this->assertEquals(303, $this->stub->getResponse()->getResponseCode());
        $this->assertEquals("/test", $this->stub->getResponse()->getRedirectUrl());
    }

    public function testRedirectWithFlashMessage() {
        FlashMessenger::reset();
        $this->assertTrue($this->stub->redirect("/", "A Test Message"));
        $this->assertEquals(array(
            "A Test Message",
        ), FlashMessenger::getMessages());
    }

    public function testRedirectRefererRedirectsToHomeWithNoReferer() {
        $this->assertTrue($this->stub->redirectReferer());
        $this->assertEquals("/", $this->stub->getResponse()->getRedirectUrl());
    }

    public function testGetFlashReturnsNullByDefault() {
        FlashMessenger::reset();
        $this->assertNull($this->stub->getFlash("foo"));
    }
        
    public function testSetFlashDefaultsValueToTrue() {
        FlashMessenger::reset();
        $this->stub->setFlash("foo");
        $this->assertTrue($this->stub->getFlash("foo"));
    }

    public function testSetFlashSupportsCustomValue() {
        FlashMessenger::reset();
        $this->stub->setFlash("foo", "bar");
        $this->assertEquals("bar", $this->stub->getFlash("foo"));
    }
}

// I'd much rather use a mock here but for some reason code coverage doesn't
// seem to work properly. Look at fixing this later.
class ConcreteController extends Controller {
    public function __construct() {
        // don't want abstract controller construct firing cos it throws an exception
        // could look at moving stuff out of construct?
        $this->request = new TestRequest();
        $this->response = new JaossResponse();
        $this->session = Session::getInstance();
    }
}
