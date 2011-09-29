<?php
class CoreException extends Exception {
	const UNKNOWN = 0;
	const URL_NOT_FOUND = 1;
	const ACTION_NOT_FOUND = 2;
	const TPL_NOT_FOUND = 3;
	const TPL_DIR_NOT_WRITABLE = 4;
    const LOG_FILE_ERROR = 5;
    const INVALID_MODE = 6;
    const CONTROLLER_CLASS_NOT_FOUND = 7;
    const PATH_REJECTED = 8;
    const VARIABLE_ALREADY_ASSIGNED = 9;
    const NO_PATHS_LOADED = 10;
    const NO_PATH_FOUND_FOR_OPTIONS = 11;
    const MODEL_CLASS_NOT_FOUND = 12;
    const COULD_NOT_ATTACH_COOKIE_JAR = 13;
    const SETTING_NOT_FOUND = 14;
    const EMPTY_CONTROLLER_FACTORY_STRING = 15;
	
	protected $args = array();
	
	public function __construct($msg = "", $code = 0, $args = array()) {
		parent::__construct($msg, $code);
		$this->args = $args;
        if ($code !== CoreException::LOG_FILE_ERROR) {
            Log::verbose("CoreException thrown [".$this->getMessage()."]");
        }
	}
	
	public function getArg($a) {
		return isset($this->args[$a]) ? $this->args[$a] : null;
	}
	
	public function ga($a) {
		return "<strong>".$this->getArg($a)."</strong>";
	}	

    public function getDefaultResponseCode() {
        $responseCodes = array(
            self::UNKNOWN => 500,
            self::URL_NOT_FOUND => 404,
            self::ACTION_NOT_FOUND => 404,
            self::TPL_NOT_FOUND => 404,
            self::TPL_DIR_NOT_WRITABLE => 500,
            self::LOG_FILE_ERROR => 500,
            self::INVALID_MODE => 500,
            self::CONTROLLER_CLASS_NOT_FOUND => 404,
            self::PATH_REJECTED => 404,
            self::VARIABLE_ALREADY_ASSIGNED => 500,
            self::NO_PATHS_LOADED => 500,
            self::NO_PATH_FOUND_FOR_OPTIONS => 404,
            self::MODEL_CLASS_NOT_FOUND => 404,
            self::COULD_NOT_ATTACH_COOKIE_JAR => 500,
            self::SETTING_NOT_FOUND => 404,
        );
        return $responseCodes[$this->getCode()];
    }
}
