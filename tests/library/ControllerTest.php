<?php
require_once(JAOSS_ROOT."tests/fixtures/controllers/concrete.php");

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
        $response = $this->stub->renderJson();

        $this->assertTrue($response instanceof JaossResponse);

        $data = json_decode(
            $response->getBody()
        );
        $this->assertEquals('OK', $data->msg);
    }

    public function testRenderJsonDoesNotAssignMsgVariableIfSet() {
        $this->stub->assign('msg', 'ERROR');

        $response = $this->stub->renderJson();

        $this->assertTrue($response instanceof JaossResponse);

        $data = json_decode(
            $response->getBody()
        );
        $this->assertEquals('ERROR', $data->msg);
    }

    public function testFactoryThrowsExceptionWhenNoClassFound() {
        try {
            Controller::factory("Unknown");
        } catch (CoreException $e) {
            $this->assertEquals("Could not find controller class 'UnknownController'", $e->getMessage());
            $this->assertEquals(CoreException::CONTROLLER_CLASS_NOT_FOUND, $e->getCode());
            return;
        }
        $this->fail("Expected exception not found");
    }

    public function testRedirectWithString() {
        $response = $this->stub->redirect("/");

        $this->assertTrue($response instanceof JaossResponse);

        $this->assertEquals(303, $response->getResponseCode());
        $this->assertEquals("/", $response->getRedirectUrl());
    }

    public function testRedirectWithValidArrayOptions() {
        // bah, we can't mock static methods, so this gets a bit long winded
        PathManager::reset();
        PathManager::loadPath("/test", "test", "Test", "test");
        list($path) = PathManager::getPaths();
        $this->stub->setPath($path);

        $response = $this->stub->redirect(array(
            "action" => "test",
        ));

        $this->assertTrue($response instanceof JaossResponse);

        $this->assertEquals(303,     $response->getResponseCode());
        $this->assertEquals("/test", $response->getRedirectUrl());
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

        $response = $this->stub->redirectAction("test");

        $this->assertTrue($response instanceof JaossResponse);

        $this->assertEquals(303,     $response->getResponseCode());
        $this->assertEquals("/test", $response->getRedirectUrl());
    }

    public function testRedirectWithFlashMessage() {
        FlashMessenger::reset();
        $response = $this->stub->redirect("/", "A Test Message");

        $this->assertEquals(array(
            "A Test Message",
        ), FlashMessenger::getMessages());
    }

    public function testRedirectRefererRedirectsToHomeWithNoReferer() {
        $response = $this->stub->redirectReferer();

        $this->assertTrue($response instanceof JaossResponse);

        $this->assertEquals("/", $response->getRedirectUrl());
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

    public function testSetResponseCodeUpdatesResponseObject() {
        $this->stub->setResponseCode(403);

        $this->assertEquals(403, $this->stub->getResponse()->getResponseCode());
    }

    public function testAddErrorWithSingleArgumentAppendsToEndOfArray() {
        $this->assertEquals(array(), $this->stub->getErrors());
        $this->stub->addError("foo");
        $this->stub->addError("bar");
        $this->assertEquals(array(
            0 => "foo",
            1 => "bar",
        ), $this->stub->getErrors());
    }

    public function testAddErrorWithTwoArgumentsSetsAssociativeArray() {
        $this->stub->addError("foo");
        $this->stub->addError("bar", "baz");
        $this->assertEquals(array(
            0 => "foo",
            "bar" => "baz",
        ), $this->stub->getErrors());
    }

    public function testSetErrorsUpdatesEntireArray() {
        $this->stub->addError("foo");

        $this->stub->setErrors(array("test" => "value"));

        $this->assertEquals(array(
            "test" => "value",
        ), $this->stub->getErrors());
    }

    public function testRedirectToExternalSiteFromRootFolderBase() {
        $response = $this->stub->redirect("http://foo.com");

        $this->assertEquals("http://foo.com", $response->getRedirectUrl());
    }

    public function testRedirectToInternalUrlFromRootFolderBase() {
        $response = $this->stub->redirect("/foo");

        $this->assertEquals("/foo", $response->getRedirectUrl());
    }

    public function testRedirectToExternalSiteFromSubfolderBase() {
        $this->stub = new ConcreteController(array(
            'base_href'  => 'http://localhost/my/subfolder/',
            'folder_base' => '/my/subfolder/',
        ));


        $response = $this->stub->redirect("http://foo.com");

        $this->assertEquals("http://foo.com", $response->getRedirectUrl());
    }

    public function testRedirectToInternalUrlFromSubfolderBase() {
        $this->stub = new ConcreteController(array(
            'base_href'  => 'http://localhost/my/subfolder/',
            'folder_base' => '/my/subfolder/',
        ));


        $response = $this->stub->redirect("/foo");

        $this->assertEquals("http://localhost/my/subfolder/foo", $response->getRedirectUrl());
    }

    public function testRedirectToInternalUrlForAjaxRequestAssignsUrlToBody() {
        $this->stub = new ConcreteController(array(
            'ajax' => true,
        ));


        $response = $this->stub->redirect("/foo");

        $data = json_decode($response->getBody(), true);

        $this->assertEquals("/foo", $data["redirect"]);
    }

    public function testRedirectToInternalUrlForAjaxRequestAssignsMessageToBody() {
        $this->stub = new ConcreteController(array(
            'ajax' => true,
        ));


        $response = $this->stub->redirect("/foo", "Test Redirect");
        $data = json_decode($response->getBody(), true);

        $this->assertEquals("Test Redirect", $data["message"]);
    }

    public function testRenderTemplateAddsHtmlContentTypeIfNotSet() {
        //
        $stub = $this->getMock("ConcreteController", array("fetchTemplate"));
        $stub->expects($this->any())
             ->method('fetchTemplate')
             ->will($this->returnValue("FooBar"));

        $response = $stub->renderTemplate("foo");

        $this->assertEquals("text/html; charset=utf-8", $response->getHeader("Content-Type"));
    }

    public function testRenderTemplateDoesNotAlterContentTypeIfNotSet() {
        //
        $stub = $this->getMock("ConcreteController", array("fetchTemplate"));
        $stub->expects($this->any())
             ->method('fetchTemplate')
             ->will($this->returnValue("FooBar"));

        $stub->addHeader("Content-Type", "foo/bar");

        $response = $stub->renderTemplate("foo");

        $this->assertEquals("foo/bar", $response->getHeader("Content-Type"));
    }

    public function testFilterRequest() {
        $stub = new ConcreteController(array(), array(
            "foo" => "bar",
            "baz" => "test",
            "invalid" => "invalid",
            "ignore" => "ignore",
        ));

        $this->assertEquals(array(
            "foo" => "bar",
            "baz" => "test",
        ), $stub->filterRequest("foo", "baz", "notfound"));
    }

    public function testFilterRequestStrict() {
        $stub = new ConcreteController(array(), array(
            "foo" => "bar",
            "baz" => "test",
            "invalid" => "invalid",
            "ignore" => "ignore",
        ));

        $this->assertEquals(array(
            "foo" => "bar",
            "baz" => "test",
            "notfound" => null,
        ), $stub->filterRequestStrict("foo", "baz", "notfound"));
    }

    public function testRenderPjaxAssignsPjaxVariable() {
        $stub = $this->getMock("ConcreteController", array("renderTemplate"));
        $stub->expects($this->any())
             ->method('renderTemplate')
             ->will($this->returnValue("FooBar"));

        $stub->renderPjax("fooBar");

        $this->assertTrue($stub->getAssignVar("_pjax"));
    }

    public function testUnassignAllEmptiesVarStack() {
        $this->stub->assign("foo", "bar");
        $this->stub->assign("baz", "test");

        $this->assertEquals(2, count($this->stub->getVarStack()));

        $this->stub->unassignAll();

        $this->assertEquals(0, count($this->stub->getVarStack()));
    }

    public function testRenderCallsRenderTemplateForNormalRequest() {
        $stub = $this->getMock("ConcreteController", array("renderTemplate"));
        $stub->expects($this->any())
             ->method('renderTemplate')
             ->will($this->returnArgument(0));

        $result = $stub->render("mock string");

        $this->assertEquals("mock string", $result);
    }

    public function testRenderAssignsErrorsVarIfAnyPresent() {
        $stub = $this->getMock("ConcreteController", array("renderTemplate"));
        $stub->expects($this->any())
             ->method('renderTemplate')
             ->will($this->returnArgument(0));

        $stub->setErrors(array(
            "foo" => "bar",
        ));

        $stub->render("mock string");

        $this->assertEquals(array(
            "foo" => "bar",
        ), $stub->getAssignVar("_errors"));
    }

    public function testRenderCallsRenderPjaxForPjaxRequest() {
        $stub = $this->getMock("ConcreteController", array("renderPjax"));
        $stub->expects($this->any())
             ->method('renderPjax')
             ->will($this->returnArgument(0));

        $stub->setRequestProperties(array(
            "pjax" => true,
        ));

        $result = $stub->render("mock string");

        $this->assertEquals("mock string", $result);
    }

    public function testRenderCallsRenderJsonForajaxRequest() {
        $this->stub->setRequestProperties(array(
            "ajax" => true,
        ));

        $this->stub->assign("foo", "bar");

        $response = $this->stub->render("dummy");

        $result = $response->getBody();

        $this->assertEquals('{"foo":"bar","msg":"OK"}', $result);
    }
}
