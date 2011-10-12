<?php
$apps = array();

// first try and load apps based on config
try {
    $apps = Settings::getValue("apps", "app");
} catch (CoreException $e) {
    // fallback on simply loading every available directory within apps
    if (defined("PROJECT_ROOT") && PROJECT_ROOT !== null) {
        $d = dir(PROJECT_ROOT."apps/");
        while (false !== ($entry = $d->read())) {
            if ($entry{0} == ".") {
                continue;
            }

            if (is_dir(PROJECT_ROOT."apps/".$entry)) {
                $apps[] = $entry;
            }
        }
    }
}

foreach ($apps as $app) {
    AppManager::loadAppFromPath($app);
}
