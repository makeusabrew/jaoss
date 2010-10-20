<?php
$apps = array();

// first try and load apps from the relevant .ini file
if (file_exists(PROJECT_ROOT."settings/apps.ini")) {
	Settings::loadFromFile(PROJECT_ROOT."settings/apps.ini");
	$apps = Settings::getValue("apps", "app");
} else {
	// fallback on simply loading every available directory within apps
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

foreach ($apps as $app) {
	AppManager::loadAppFromPath($app);
}
