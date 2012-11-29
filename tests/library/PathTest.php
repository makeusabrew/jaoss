<?php

class PathTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->path = new JaossPath();
    }

    public function testSetAndGetAction() {
        $this->path->setAction("myaction");
        $this->assertEquals("myaction", $this->path->getAction());
        $this->path->setAction("anotheraction");
        $this->assertEquals("anotheraction", $this->path->getAction());
    }

    public function testSetAndGetController() {
        $this->path->setController("mycontroller");
        $this->assertEquals("mycontroller", $this->path->getController());
        $this->path->setController("anothercontroller");
        $this->assertEquals("anothercontroller", $this->path->getController());
    }

    public function testSetAndGetApp() {
        $this->path->setApp("app");
        $this->assertEquals("app", $this->path->getApp());
    }

    public function testSetAndGetPattern() {
        $this->path->setPattern("^/foo$");
        $this->assertEquals("^/foo$", $this->path->getPattern());
    }

    public function testSetAndGetName() {
        $this->path->setName("my_name");
        $this->assertEquals("my_name", $this->path->getName());
        $this->path->setName("another_name");
        $this->assertEquals("another_name", $this->path->getName());
    }

    public function testGetMatchReturnsNullWithNonMatchingIndex() {
        $this->assertNull($this->path->getMatch('fake'));
    }

    public function testGetMatchWithMatchingIndex() {
        $this->path->setMatches(array(
            'foo' => 'bar',
        ));

        $this->assertEquals('bar', $this->path->getMatch('foo'));
    }

    public function testSetAndGetCacheTtl() {
        $this->assertNull($this->path->getCacheTtl());
        $this->path->setCacheTtl(60);
        $this->assertEquals(60, $this->path->getCacheTtl());
    }

    public function testSetAndGetIsCacheable() {
        $this->assertFalse($this->path->isCacheable());
        $this->path->setCacheable(true);
        $this->assertTrue($this->path->isCacheable());
    }

    public function testRunThrowsExceptionWithNullExistingAppAndController() {
        try {
            $this->path->run();
        } catch (CoreException $e) {
            $this->assertEquals(CoreException::EMPTY_CONTROLLER_FACTORY_STRING, $e->getCode());
            return;
        }

        $this->fail("Expected exception not thrown");
    }

    public function testRunThrowsExceptionWithNotFoundController() {
        $this->path->setController("DoesNotExist");
        try {
            $this->path->run();
        } catch (CoreException $e) {
            $this->assertEquals(CoreException::CONTROLLER_CLASS_NOT_FOUND, $e->getCode());
            return;
        }

        $this->fail("Expected exception not thrown");
    }

    public function testToArrayForEmptyPath() {
        $this->assertEquals(array(
            "pattern" => null,
            "app" => null,
            "controller" => null,
            "action" => null,
            "name" => null,
            "cacheTtl" => null,
            "requestMethods" => array(),
        ), $this->path->toArray());
    }

    public function testToArrayForPopulatedPath() {

        $this->path->setPattern("/foo");
        $this->path->setApp("bar");
        $this->path->setController("baz");
        $this->path->setAction("action");
        $this->path->setName("my:name");
        $this->path->setCacheTtl(123);
        $this->path->setRequestMethods(array("get"));

        $this->assertEquals(array(
            "pattern" => "/foo",
            "app" => "bar",
            "controller" => "baz",
            "action" => "action",
            "name" => "my:name",
            "cacheTtl" => 123,
            "requestMethods" => array("GET"),
        ), $this->path->toArray());
    }

    public function testSetFromArray() {

        $this->path->setFromArray(array(
            "pattern" => "/foo",
            "app" => "bar",
            "controller" => "baz",
            "action" => "action",
            "name" => "my:name",
            "cacheTtl" => 123,
            "requestMethods" => array("GET"),
        ));

        $this->assertEquals("/foo", $this->path->getPattern());
        $this->assertEquals("bar", $this->path->getApp());
        $this->assertEquals("baz", $this->path->getController());
        $this->assertEquals("action", $this->path->getAction());
        $this->assertEquals("my:name", $this->path->getName());
        $this->assertEquals(123, $this->path->getCacheTtl());
        $this->assertEquals(array("GET"), $this->path->getRequestMethods());
    }

}
