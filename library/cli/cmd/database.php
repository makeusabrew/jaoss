<?php
include("library/db/migration.php");
class Cli_Database extends Cli {

    public function run() {
        if (count($this->args) === 0) {
            // ok, interactive
            $method = $this->promptOptions('Please choose an option', array(
                1 => 'migrate',
                2 => 'backup',
            ));
        } else {
            $method = $this->shiftArg();
        }
        $this->$method();
    }

    protected function backup() {
        $user = Settings::getValue("db.user");
        $host = Settings::getValue("db.host");
        $pass = Settings::getValue("db.pass");
        $db   = Settings::getValue("db.dbname");

        $path = "db-".date("Y-m-d-h-i-s").".sql";

        $cmd = "mysqldump -u ".($user)." -h ".($host)." -p".($pass)." ".($db)." > ".$path;

        $this->exec($cmd, "Dumping database to ".$path);
    }

    protected function migrate() {
        $dbh = Db::getInstance();

        $sth = $dbh->prepare("SELECT * FROM `schema_changelog` ORDER BY `id` DESC LIMIT 1");
        $sth->execute();
        
        $result = $sth->fetch();

        if ($result === false) {
            $currentVersion = 0;
        } else {
            $currentVersion = $result['version'];
        }

        $this->writeLine("Current schema version: ".$currentVersion);

        $migrations = array();

        foreach (glob(PROJECT_ROOT."migrations/*.php") as $file) {
            if (!preg_match("#migrations/migration\.(\d{4})\.php$#", $file, $matches)) {
                $this->writeLine(
                    "File [".$file."] does not follow correct naming convention",
                    Colours::RED
                );
                continue;
            }
            require_once($file);

            $version = intval($matches[1]);

            $class = "MigrationV".$version;
            if (!class_exists($class)) {
                $this->writeLine(
                    "Class [".$class."] does not exist in [".$file."]",
                    Colours::RED
                );
                continue;
            }

            $instance = new $class;

            if (!($instance instanceof AbstractMigration)) {
                $this->writeLine(
                    "Class [".$class."] does not extend AbstractMigration",
                    Colours::RED
                );
                continue;
            }

            $migrations[$version] = $instance;
        }

        ksort($migrations);

        $headVersion = 0;

        $finalVersions = array();
        foreach ($migrations as $version => $migration) {

            $headVersion = $version;

            if ($version > $currentVersion) {
                $finalMigrations[$version] = $migration;
            }
        }

        $this->writeLine("Current HEAD: " .$headVersion);

        if ($currentVersion >= $headVersion) {
            $this->writeLine("No migration required");
            return;
        }

        $this->writeLine("Migration required from ".$currentVersion." to ".$headVersion);
        $this->writeLine("Queries required:");

        $this->writeLine();

        foreach ($finalMigrations as $version => $migration) {
            foreach ($migration->getQueries() as $query) {
                $this->writeLine(
                    "[".$version."] - ".$query['sql']." => (".implode(",", $query['params']).")",
                    Colours::YELLOW
                );
            }
        }

        $this->writeLine();

        $result = $this->prompt("Continue?", "y");

        if ($result !== 'y') {
            $this->writeLine("Aborting");
            return;
        }

        $this->writeLine("Starting transaction");
        $sth = $dbh->prepare("START TRANSACTION");
        $sth->execute();

        try {
            foreach ($finalMigrations as $version => $migration) {
                foreach ($migration->getQueries() as $query) {
                    // migration
                    $sth = $dbh->prepare($query['sql']);
                    $sth->execute($query['params']);
                }

                // log
                $sql = "INSERT INTO `schema_changelog`
                    (`created`,`title`,`content`,`version`)
                    VALUES (?, ?, ?, ?)";

                $params = array(
                    Utils::getDate("Y-m-d H:i:s"),
                    $migration->getTitle(),
                    $migration->getDescription(),
                    $version,
                );

                $st2 = $dbh->prepare($sql);
                $st2->execute($params);
            }
        } catch (PDOException $e) {
            $this->writeLine(
                "Caught PDO exception: ".$e->getMessage(),
                Colours::RED
            );

            $this->writeLine("Rollback");
            $sth = $dbh->prepare("ROLLBACK");
            $sth->execute();

            throw new CliException("DB migration failed", 1);
        }

        $this->writeLine("Committing");
        $sth = $dbh->prepare("COMMIT");
        $sth->execute();
    }
}
