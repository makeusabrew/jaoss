<?php
/*
class CacheTest extends PHPUnit_Framework_TestCase {
    // this test actually just checks that we deal with a bug
    // in the APC library which throws an error when writing
    // to the same key twice
    public function testStoringSameKeySwallowsAPCErrorException() {
        $this->assertTrue(Cache::store("test_key_1234", "Foo"));
        $this->assertTrue(Cache::store("test_key_1234", "Bar"));

        $success = null;

        $this->assertEquals("Bar", Cache::fetch("test_key_1234", $success));
        $this->assertTrue($success);
    }

}
*/
