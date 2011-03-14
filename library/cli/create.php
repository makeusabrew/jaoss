<?php
class Cli_Create extends Cli {

    public function run($args) {
        if (count($args) === 0) {
            // ok, interactive
            $method = $this->promptOptions('Please choose an option', array(
                'project',
                'app',
            ));
        } else {
            $method = array_shift($args);
        }
        $this->$method($args);
    }

    protected function project($args) {
        if (count($args) === 0) {
            // no problemo, go interactive
            $dir = $this->prompt('Please choose a project directory', getcwd());
        } else {
            $dir = array_shift($args);
        }

        $this->exec(
            'git clone --recursive git://github.com/makeusabrew/jaoss-web-template.git '.escapeshellarg($dir),
            'Cloning jaoss web template github repo into folder ['.$dir.']'
        );
    }
}
