<?php
Settings::loadFromFile("settings/build.ini");

if (!is_writable(Settings::getValue("smarty", "compile_dir"))) {
	throw new CoreException("Smarty compile directory is not writable");
}
