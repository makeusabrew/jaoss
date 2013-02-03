<?php

abstract class AssetPipeline {
    protected static $instance = null;

    protected $files = array();
    protected $options = array();
    protected $folderBase = "assets";
    protected $usedFilters = array();
    protected $output = "";

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

    protected function useFilter($filter) {
        $this->usedFilters[$filter] = true;
    }

    protected function clearFilter($filter) {
        unset($this->usedFilters[$filter]);
    }

    protected function usedFilter($filter) {
        return isset($this->usedFilters[$filter]);
    }

    public function getWebPath() {
        return "/".$this->getOutputPath();
    }

    public function getFilePath() {
        return PROJECT_ROOT."public/".$this->getOutputPath();
    }

    protected function getOutputPath() {
        $outputFile = $this->getOption("output");
        return $this->folderBase."/".$this->getType()."/".$outputFile.".".$this->getType();
    }

    abstract protected function getType();
    abstract public function getHtmlTag();
    abstract public function pipe($filter);
    abstract public function finalise();
}
