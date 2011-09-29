<?php

abstract class Cli {

    const MIN_ARG_COUNT = 2;
    
    protected $args = array();

    public static function factory($argc, $argv, $mode) {
        if ($argc < self::MIN_ARG_COUNT) {
            throw new Exception(
                "Insufficient arguments",
                1
            );
        }

        // it feels bad to ditch the first arg, but we really don't care about it
        array_shift($argv);

        $path = JAOSS_ROOT."library/cli/".strtolower($argv[0]).".php";
        if (!file_exists($path)) {
            throw new CliException(
                "Path ".$path." does not exist",
                1
            );
        }

        require_once($path);

        $class = "Cli_".ucfirst($argv[0]);
        if (!class_exists($class)) {
            throw new CliException(
                "Class ".$class." does not exist",
                1
            );
        }

        array_shift($argv);

        $object = new $class;
        $object->setArgs($argv);
        $object->run();
    }

    protected function readLine() {
        return trim(fgets(STDIN));
    }

    protected function write($data) {
        fwrite(STDOUT, $data);
    }

    protected function writeLine($data) {
        $this->write($data."\n");
    }

    protected function prompt($prompt, $default = null) {
        $out = $prompt;
        if ($default !== null) {
            $out .= " [".$default."]";
        }
        $out .= ": ";
        $this->write($out);
        $val = $this->readLine();
        if ($val === '') {
            return $default;
        }
        return $val;
    }

    protected function promptOptions($prompt, $options, $default = null) {
        $out = $prompt."\n";
        foreach ($options as $option) {
            $out .= "\t".$option."\n";
        }
        return $this->prompt($out, $default);
    }

    protected function exec($cmd, $desc = null) {
        $output = array();
        $retVal = null;
        if ($desc !== null) {
            $this->writeLine($desc);
        }
        exec($cmd, $output, $retVal);
        if ($retVal !== 0) {
            throw new CliException(
                'Non zero return status',
                $retVal
            );
        }
        return $this;
    }

    protected function hasArg($arg) {
        return (is_array($this->args) && in_array($arg, $this->args));
    }

    protected function shiftArg() {
        return array_shift($this->args);
    }

    abstract public function run();
    
    public function setArgs($args) {
        $this->args = $args;
    }
}
