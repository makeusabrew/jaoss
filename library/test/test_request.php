<?php
class TestRequest extends Request {
    //@todo we could extend this request class instead and define these (and other) methods there?
    public function setParams($params = array()) {
        if (!$this->sapi == "cli") {
            Log::debug("attempting to set request params via non CLI server API!");
            return false;
        }
        $allowed_params = array("folder_base", "base_href", "url", "query_string", "method", "ajax", "referer");
        foreach ($allowed_params as $param) {
            if (isset($params[$param])) {
                $this->$param = $params[$param];
            }
        }
        return true;
    }
	
}
