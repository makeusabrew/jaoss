<?php
class Email {
    protected $headers = array();
    protected $to;
    protected $subject;
    protected $from;
    protected $body;
    protected $smarty;

    public static function factory() {
        // pretty pointless for now. @todo improve to take note of mode etc
        return new Email();
    }

    public function setTo($to) {
        $this->to = $to;
    }

    public function setSubject($subject) {
        $this->subject = $subject;
    }

    public function setFrom($from) {
        $this->from = $from;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    public function send() {
        $canSend = true;
        if ($this->to == null) {
            Log::debug("to field is blank");
            $canSend = false;
        }

        if ($this->from == null) {
            Log::debug("from field is blank");
            $canSend = false;
        }

        if ($this->subject == null) {
            Log::debug("subject is blank");
            $canSend = false;
        }

        if ($this->body == null) {
            Log::debug("body is blank");
            $canSend = false;
        }

        if ($canSend) {
            $this->setHeader("From", $this->from);
            Log::debug("sending mail to [".$this->getToAsString()."], from [".$this->from."], subject [".$this->subject."], body length [".strlen($this->body)."]");
            return mail($this->getToAsString(), $this->subject, $this->body, $this->getHeadersAsString());
        }
        return false;
    }

    public function setHeader($header, $value) {
        $this->headers[] = $header.": ".$value;
    }

    public function getHeadersAsString() {
        $str = "";
        foreach ($this->headers as $header) {
            $str .= $header."\r\n";
        }
        $str = substr($str, 0, -2);
        return $str;
    }

    public function getToAsString() {
        if (is_array($this->to)) {
            $str = "";
            foreach ($this->to as $to) {
                $str .= $to.",";
            }
            $str = substr($str, 0, -1);
            return $str;
        }
        return $this->to;
    }

    public function setHtmlHeaders() {
        $this->setHeader("MIME-Version", "1.0");
        $this->setHeader("Content-type", "text/html; charset=UTF-8");
    }

    public function setBodyFromTemplate($template, $params = array()) {
        if ($this->smarty === null) {
            $this->smarty = new Smarty();
            
            $apps = AppManager::getAppPaths();
            $tpl_dirs = array(PROJECT_ROOT."apps/");
            foreach ($apps as $app) {
                $tpl_dirs[] = PROJECT_ROOT."apps/{$app}/views/";
            }
            
            $this->smarty->template_dir	= $tpl_dirs;
            $this->smarty->compile_dir = Settings::getValue("smarty", "compile_dir");
        }
		if ($this->smarty->templateExists($template.".tpl")) {
            $this->smarty->assign("base_href", JaossRequest::getInstance()->getBaseHref());
            $this->smarty->assign("current_url", JaossRequest::getInstance()->getUrl());
            foreach ($params as $var => $val) {
                $this->smarty->assign($var, $val);
            }
			$this->setBody($this->smarty->fetch($template.".tpl"));
            return true;
		}

        throw new CoreException(
            "Template Not Found",
            CoreException::TPL_NOT_FOUND,
            array(
                "paths" => $this->smarty->template_dir,
                "tpl" => $template,
            )
        );
        
    }
}
