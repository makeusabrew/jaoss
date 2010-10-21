<?php
class RequestTest extends PHPUnit_Framework_TestCase {
    public function testRequest() {
        //@todo testing the request object requires alot more thought - we DO need to find
        //a way to actually test it - at the moment it just detects CLI and bombs out
        //hence at least for now the following asserts are valid, but only because of that
        $request = JaossRequest::getInstance();

        $this->assertNull($request->getMethod());
        $this->assertNull($request->getBaseHref());
        $this->assertNull($request->getUrl());
        $this->assertNull($request->getReferer());
        $this->assertNull($request->getQueryString());

        $this->assertFalse($request->isGet());
        $this->assertFalse($request->isPost());
        $this->assertFalse($request->isAjax());
    }
}
