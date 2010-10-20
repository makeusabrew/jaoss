<?php
Settings::loadFromFile(PROJECT_ROOT."settings/build.ini");

if (!is_writable(Settings::getValue("smarty", "compile_dir"))) {
	throw new CoreException(
        "Smarty compile directory is not writable",
        CoreException::TPL_DIR_NOT_WRITABLE,
        array("dir" => Settings::getValue("smarty", "compile_dir"))
    );
}
