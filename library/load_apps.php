<?php
$apps = array();

// first try and load apps from the relevant .ini file
if (file_exists("settings/apps.ini")) {
	Settings::loadFromFile("settings/apps.ini");
	$apps = Settings::getValue("apps", "app");
} else {
	// fallback on simply loading every available directory within apps
	$d = dir("apps/");
	while (false !== ($entry = $d->read())) {
		if ($entry{0} == ".") {
			continue;
		}

		if (is_dir("apps/".$entry)) {
			$apps[] = $entry;
		}
	}
}

foreach ($apps as $app) {
	AppManager::loadAppFromPath($app);
}
