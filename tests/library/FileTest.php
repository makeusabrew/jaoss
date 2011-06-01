<?php

class FileTest extends PHPUnit_Framework_TestCase {

    public function testErrorsWithNoData() {
        $file = new File();
        $this->assertEquals(File::ERR_NO_FILE, $file->getError());
        $this->assertEquals("No file uploaded", $file->getMessage());
    }

    public function testGetFilename() {
        $file = new File(array(
            "name" => "testfile.jpeg",
            "type" => "image/jpeg",
            "tmp_name" => "/tmp/fake12345",
            "error" => 0,
            "size" => 123456,
        ));
        $this->assertEquals("testfile.jpeg", $file->getFileName());
    }

    public function testGetFileExt() {
        $file = new File(array(
            "name" => "testfile.jpeg",
            "type" => "image/jpeg",
            "tmp_name" => "/tmp/fake12345",
            "error" => 0,
            "size" => 123456,
        ));
        $this->assertEquals("jpeg", $file->getFileExt());
    }
}
