<?php
class Cli_List extends Cli {

    public function run() {
        if (count($this->args) === 0) {
            // ok, interactive
            $method = $this->promptOptions('Please choose an option', array(
                1 => 'paths',
            ));
        } else {
            $method = $this->shiftArg();
        }
        $this->$method();
    }

    protected function paths() {
        $paths = PathManager::getPaths();

        $this->setOutputColour(Colours::YELLOW);
        $str = count($paths)." paths available";
        $underline = str_repeat("-", strlen($str));
        $this->writeLine($str);
        $this->writeLine($underline);
        $this->write("\n");
        foreach ($paths as $path) {
            $this->writeLine("Pattern   : ".$path->getPattern());
            $this->writeLine("App       : ".$path->getApp());
            $this->writeLine("Controller: ".$path->getController());
            $this->writeLine("Action    : ".$path->getAction());
            $this->writeLine("Cacheable : ".($path->isCacheable() ? "Yes" : "No"));
            if ($path->isCacheable()) {
                $this->writeLine("Cache TTL : ".$path->getCacheTtl());
            }
            $this->write("\n");
        }
    }
}
