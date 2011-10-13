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
    AppManager::installApp($app);
}

if (Cache::isEnabled()) {
    Log::debug("Cache enabled - attempting to fetch paths from cache");
    $pathKey = Settings::getValue("site", "namespace").AppManager::getInstalledAppsHash();

    $success = false;
    $paths = Cache::fetch($pathKey, $success);
    if ($success === true) {
        Log::debug("Got [".count($paths)."] paths from cache");
        PathManager::setPaths($paths);
    } else {
        Log::debug("Cache path miss - loading paths and storing in cache");
        AppManager::loadAppPaths();
        Cache::store($pathKey, PathManager::getPaths(), 300);
    }
} else {
    AppManager::loadAppPaths();
}
