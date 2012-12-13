<?php

abstract class AssetPipeline {
    protected static $instance = null;

    protected $files = array();
    protected $options = array();
    protected $folderBase = "assets";

    public static function getInstance() {
        if (static::$instance === null) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    public function addFile($file) {
        $this->files[] = $file;
    }

    public function setOptions($options) {
        $this->options = $options;
    }

    protected function getOption($key) {
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }

    public function getOutputPath() {
        $outputFile = $this->getOption("output");

        if ($this->getOption("compress")) {
            $outputFile .= ".min";
        }

        return "/".$this->folderBase."/".$this->getType()."/".$outputFile.".".$this->getType();
    }

    abstract protected function getType();
    abstract public function getHtmlTag();
}
