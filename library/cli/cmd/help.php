<?php

class Cli_Help extends Cli {
    public function run() {
        $this->writeLine("usage: jcli <command> [<args>]");
        $this->write("\n");
        $this->writeLine("In most cases, <args> can be omitted and will be prompted for in interactive mode if required.");
        $this->write("\n");
        $this->writeLine("The available commands are:");
        $this->writeLine("  create project [<dir>]");
        $this->writeLine("  create app [<folder>] [--model=Object]");
        $this->writeLine("  table create [<Table>] [--output-only]");
        $this->writeLine("  table sync [<Table>] [--output-only]");
        $this->writeLine("  dispatch <url> [--no-render]");
        $this->writeLine("  fixture import-from-db [<file>]");
        $this->writeLine("  fixture update-db [<file>]");
        $this->writeLine("  list paths");
        $this->write("\n");
    }
}
