<?php

class PHPUnitTestController extends PHPUnit_Framework_TestCase {
    protected $request = null;
    public function setUp() {
        //
        $this->request = new TestRequest(); 
    }

    public function tearDown() {
        $this->request = null;
    }
}
