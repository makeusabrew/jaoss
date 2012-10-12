<?php
class RejectedExceptionTest extends PHPUnit_Framework_TestCase {
    public function testCodeIsAlwaysPathRejected() {
        $e = new RejectedException("Test");

        $this->assertEquals(CoreException::PATH_REJECTED, $e->getCode());
    }
}
