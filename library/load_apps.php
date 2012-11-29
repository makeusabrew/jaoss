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
    $cache = Cache::getInstance();

    Log::debug("Cache enabled - attempting to fetch paths from cache");
    $pathKey = Settings::getValue("site", "namespace").AppManager::getInstalledAppsHash();

    $paths = $cache->fetch($pathKey);

    if ($cache->fetchHit()) {
        Log::debug("Got [".count($paths)."] paths from cache");

        PathManager::setPathsFromArray($paths);
    } else {
        Log::debug("Cache path miss - loading paths and storing in cache");

        AppManager::loadAppPaths();
        $cache->store($pathKey, PathManager::getPathsToArray(), 300);
    }
} else {
    AppManager::loadAppPaths();
}
