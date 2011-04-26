<?php

abstract class Cli {

    const MIN_ARG_COUNT = 2;

    public static function factory($argc, $argv) {
        if ($argc < self::MIN_ARG_COUNT) {
            throw new Exception(
                "Insufficient arguments",
                1
            );
        }

        // it feels bad to ditch the first arg, but we really don't care about it
        array_shift($argv);

        $class = "Cli_".ucfirst($argv[0]);
        if (!class_exists($class)) {
            throw new CliException(
                "Class ".$class." does not exist",
                1
            );
        }

        array_shift($argv);

        $object = new $class;
        $object->run($argv);
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
}
