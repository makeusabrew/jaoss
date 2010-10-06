<?php

class File {
    protected $name;
    protected $type;
    protected $tmp_name;
    protected $error;
    protected $size;

    const ERR_NO_FILE = 99;

    public function __construct($data = null) {
        // we assume this is a $_FILES array index
        if ($data === null) {
            $this->error = File::ERR_NO_FILE;
            return;
        }
        $this->name = $data["name"];
        $this->type = $data["type"];
        $this->tmp_name = $data["tmp_name"];
        $this->error = $data["error"];
        $this->size = $data["size"];
    }
    
    public function getError() {
        return $this->error;
    }

    public function getMessage() {
        switch ($this->error) {
            case UPLOAD_ERR_OK:
                return "The file was uploaded successfully";
            case File::ERR_NO_FILE:
                return "No file uploaded";
            default:
                return "Unknown error";
        }
    }

    public function commitFile($path, $name = null) {
        if ($name === null) {
            $name = $this->getFilename();
        } else {
            $name .= ".".$this->getFileExt();
        }

        if (!is_dir($path)) {
            throw new CoreException("Upload path does not exist");
        }

        if (!is_writable($path)) {
            throw new CoreException("Upload path is not writable");
        }

        Log::debug("moving uploaded file [".$this->tmp_name."] to destination [".$path.$name."]");
        move_uploaded_file($this->tmp_name, $path.$name);
    }

    public function getFilename() {
        return $this->name;
    }

    public function getFileExt() {
        $pos = strrpos($this->getFilename(), ".");
        if ($pos === false) {
            return "";
        }
        return substr($this->getFilename(), $pos+1);
    }
}
