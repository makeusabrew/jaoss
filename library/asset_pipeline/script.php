<?php
class ScriptPipeline extends AssetPipeline {
    protected function getType() {
        return "js";
    }

    public function getHtmlTag() {
        return "<script src=\"".$this->getOutputPath()."\"></script>";
    }
}
