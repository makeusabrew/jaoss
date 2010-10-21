<?php

class PathTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->path = new Path();
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
}
