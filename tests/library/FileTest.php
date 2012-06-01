<?php

class FileTest extends PHPUnit_Framework_TestCase {

    protected $fileData = array(
        "name" => "testfile.jpeg",
        "type" => "image/jpeg",
        "tmp_name" => "/tmp/fake12345",
        "error" => 0,
        "size" => 123456,
    );

    public function testErrorsWithNoData() {
        $file = new File();
        $this->assertEquals(File::ERR_NO_FILE, $file->getError());
        $this->assertEquals("No file uploaded", $file->getMessage());
    }

    public function testGetFilename() {
        $file = new File($this->fileData);
        $this->assertEquals("testfile.jpeg", $file->getFileName());
    }

    public function testGetFileExt() {
        $file = new File($this->fileData);
        $this->assertEquals("jpeg", $file->getFileExt());
    }

    public function testGetFileExtWithNoDotInFilename() {
        $this->fileData['name'] = 'myFile';
        $file = new File($this->fileData);
        $this->assertEquals("", $file->getFileExt());
    }

    public function testGetMessage() {
        $file = new File($this->fileData);
        $this->assertEquals("The file was uploaded successfully", $file->getMessage());

        $this->fileData['error'] = 99;
        $file = new File($this->fileData);
        $this->assertEquals("No file uploaded", $file->getMessage());

        $this->fileData['error'] = 99999;
        $file = new File($this->fileData);
        $this->assertEquals("Unknown error", $file->getMessage());
    }

    public function testGetMessageErrIniSize() {
        $this->fileData['error'] = UPLOAD_ERR_INI_SIZE;
        $file = new File($this->fileData);
        $this->assertEquals("The uploaded file is too large", $file->getMessage());
    }
}
