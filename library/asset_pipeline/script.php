<?php
class ScriptPipeline extends AssetPipeline {
    protected function getType() {
        return "js";
    }

    public function getHtmlTag() {
        return "<script src=\"".$this->getWebPath()."\"></script>";
    }

    // @todo how do we ensure filters know about each other?
    // for example, if we minify that affects where we write to, whether we
    // need a tmp file etc
    public function pipe($filter) {
        $this->useFilter($filter);
        switch ($filter) {
            case "concat":
                return $this->concatenate();

            case "minify":
                return $this->minify();

            default:
                $this->clearFilter($filter);
                throw new CoreException("Unknown pipeline filter: ".$filter);
        }
    }

    protected function concatenate() {
        $data = "";
        foreach ($this->files as $file) {
            $data .= file_get_contents(PROJECT_ROOT.$file);
            $data .= "\n/***/\n";
        }
        $this->output .= $data;
    }

    protected function minify() {
        $output = array();
        // @todo
        $this->output = implode(PHP_EOL, $output);
    }

    public function finalise() {
        $fp = fopen($this->getFilePath(), "w");
        fwrite($fp, $this->output);
        fclose($fp);
    }
}
