<?php

abstract class Cli {

    const MIN_ARG_COUNT = 2;
    
    protected $args = array();
    protected $outputColour = null;

    public static function factory($argc, $argv, $mode) {
        if ($argc < self::MIN_ARG_COUNT) {
            throw new Exception(
                "Insufficient arguments. Run with --help for a list of available commands.",
                1
            );
        }

        // it feels bad to ditch the first arg, but we really don't care about it
        array_shift($argv);

        // allow varying ways of accessing help
        if ($argv[0] == "--help" || $argv[0] == "-h") {
            $argv[0] = "help";
        }

        $path = null;
        if (defined("PROJECT_ROOT")) {
            $apps = AppManager::getAppPaths();
            foreach ($apps as $app) {
                $path = PROJECT_ROOT."apps/".$app."/cli/".strtolower($argv[0]).".php";
                if (file_exists($path)) {
                    break;
                } else {
                    $path = null;
                }
            }
        }
        if ($path === null) {
            $path = JAOSS_ROOT."library/cli/cmd/".strtolower($argv[0]).".php";
            if (!file_exists($path)) {
                throw new CliException(
                    "Path ".$path." does not exist",
                    1
                );
            }
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
        return $object;
    }

    protected function readLine() {
        return trim(fgets(STDIN));
    }

    protected function write($data, $colour = null) {
        $colourCode = null;
        if ($colour !== null) {
            $colourCode = $colour;
        } else if ($this->outputColour !== null) {
            $colourCode = $this->outputColour;
        }

        if ($colourCode !== null) {
            $data = Colours::colour($data, $colourCode);
        }
        fwrite(STDOUT, $data);
    }

    protected function writeLine($data, $colour = null) {
        $this->write($data."\n", $colour);
    }

    protected function prompt($prompt, $default = null, $shortcuts = array()) {
        $out = $prompt;
        if ($default !== null) {
            $out .= " [".$default."]";
        }
        $out .= ": ";
        $this->write($out);
        $val = $this->readLine();
        if ($val === '') {
            return $default;
        } else if (count($shortcuts) && isset($shortcuts[$val])) {
            return $shortcuts[$val];
        }
        return $val;
    }

    protected function promptOptions($prompt, $options, $default = null) {
        $out = $prompt."\n";
        $shortcuts = array();

        foreach ($options as $key => $option) {
            $out .= "\t".$key.") ".$option."\n";
            $shortcuts[$key] = $option;
        }
        return $this->prompt($out, $default, $shortcuts);
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
        foreach ($this->args as $_arg) {
            if (preg_match("#(--[a-z-]+)(=([a-z-]+))?#", $_arg, $matches)) {
                if ($matches[1] == $arg) {
                    return true;
                }
            } else if ($_arg == $arg) {
                return true;
            }
        }
        return false;
    }

    protected function getArgValue($arg) {
        foreach ($this->args as $_arg) {
            if (preg_match("#(--[a-z-]+)=([A-z-]+)#", $_arg, $matches)) {
                return $matches[2];
            }
        }
        return null;
    }

    protected function shiftArg() {
        return array_shift($this->args);
    }

    abstract public function run();
    
    public function setArgs($args) {
        $this->args = $args;
    }

    public function setOutputColour($colour) {
        $this->outputColour = $colour;
    }

    public function clearOutputColour() {
        $this->outputColour = null;
    }

    public function __call($name, $arguments) {
        // well, if we got here then the method wasn't valid. Try a few known tweaks
        $method = str_replace("-", "_", $name);
        if (method_exists($this, $method) &&
            is_callable(array($this, $method))) {
            return $this->$method($arguments);
        }

        throw new CliException("Method [".$name."] does not exist", 1);
    }
}
