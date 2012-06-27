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

    public function testSetAndGetLocation() {
        $this->path->setLocation("mylocation");
        $this->assertEquals("mylocation", $this->path->getLocation());
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

}
