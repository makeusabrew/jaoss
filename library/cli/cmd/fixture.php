<?php
class Cli_Fixture extends Cli {

    public function run() {
        if (count($this->args) === 0) {
            // ok, interactive
            $method = $this->promptOptions('Please choose an option', array(
                1 => 'update',
                2 => 'import',
            ));
        } else {
            $method = $this->shiftArg();
        }
        $this->$method();
    }

    protected function update() {
        $fixture = $this->getFixtureFile();

        if (!is_writable($fixture)) {
            throw new CliException(
                "Fixture file [".$fixture."] is not writable",
                1
            );
        }

        // okay! Off we go!
        $user = Settings::getValue("db.user");
        $host = Settings::getValue("db.host");
        $pass = Settings::getValue("db.pass");
        $db = Settings::getValue("db.dbname");
        $cmd = "mysqldump -u ".$user." -p".$pass." -h ".$host." ".$db." > ".$fixture;
        $cmdMasked = str_replace($pass, str_repeat("*", strlen($pass)), $cmd);

        $this->exec($cmd, Colours::yellow($cmdMasked));
    }

    protected function import() {
        $fixture = $this->getFixtureFile();

        $user = Settings::getValue("db.user");
        $host = Settings::getValue("db.host");
        $pass = Settings::getValue("db.pass");
        $db = Settings::getValue("db.dbname");
        $cmd = "mysql -u ".$user." -p".$pass." -h ".$host." ".$db." < ".$fixture;
        $cmdMasked = str_replace($pass, str_repeat("*", strlen($pass)), $cmd);

        $this->exec($cmd, Colours::yellow($cmdMasked));
    }

    protected function getFixtureFile() {
        if (count($this->args) === 0) {
            $this->writeLine("Looking for fixtures in tests/fixtures/*.sql");
            $fixtures = glob(PROJECT_ROOT."tests/fixtures/*.sql");
            if (count($fixtures) === 0) {
                throw new CliException(
                    "No test fixtures found!",
                    1
                );
            } else if (count($fixtures) === 1) {
                // superb. perfect outcome!
                $fixture = $fixtures[0];
                $this->writeLine("Found single fixture file: ".$fixture, Colours::GREEN);
            } else {
                $fixture = $this->promptOptions('Please choose a fixture file to update', $fixtures);
            }
        } else {
            $fixture = $this->shiftArg();
        }

        // we don't know how we got fixture, so check it exists
        if (!file_exists($fixture)) {
            throw new CliException(
                "Fixture file [".$fixture."] not found",
                1
            );
        }
        return $fixture;
    }
}
