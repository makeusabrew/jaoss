<?php
class Email {
    protected $headers = array();
    protected $to;
    protected $subject;
    protected $from;
    protected $body;
    protected $smarty;
    protected $handler;

    public static function factory() {
        $email = new Email();
        try {
            $handler = Settings::getValue("email.handler");
        } catch (CoreException $e) {
            $handler = "default";
        }
        $email->handler = EmailHandler::factory($handler);
        return $email;
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
            Log::debug("sending mail to [".$this->getToAsString()."], from [".$this->getFrom()."], subject [".$this->getSubject()."], body length [".strlen($this->getBody())."]");
            return $this->handler->send($this->getToAsString(), $this->getSubject(), $this->getBody(), $this->getHeadersAsString(), $this->getFrom());
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
        if ($this->to === null) {
            return "";
        }
        return $this->to;
    }

    public function getFrom() {
        return $this->from;
    }

    public function getBody() {
        return $this->body;
    }

    public function getSubject() {
        return $this->subject;
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
            $this->smarty->plugins_dir = array(
                JAOSS_ROOT."library/Smarty/libs/plugins",  // default smarty dir
                JAOSS_ROOT."library/Smarty/custom_plugins",
            );
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

interface IEmailHandler {
    public function send($to, $subject, $body, $headers, $from);
}

abstract class EmailHandler {

    public static function factory($mode) {
        $prefix = ucfirst(strtolower($mode));
        if (class_exists($prefix."EmailHandler")) {
            $class = $prefix."EmailHandler";
            return new $class;
        }
        return null;
    }
}

class DefaultEmailHandler implements IEmailHandler {
    public function send($to, $subject, $body, $headers, $from) {
        return mail($to, $subject, $body, $headers);
    }
}

class TestEmailHandler implements IEmailHandler {
    protected static $sentEmails = array();

    public static function resetSentEmails() {
        self::$sentEmails = array();
    }

    public static function getSentEmails() {
        return self::$sentEmails;
    }

    public function send($to, $subject, $body, $headers, $from) {
        // this could obviously be improved!
        $email = array(
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'headers' => $headers,
            'from' => $from,
        );
        self::$sentEmails[] = $email;

        try {
            $outputDir = Settings::getValue("email.output_dir");
        } catch (CoreException $e) {
            Log::debug("No email.output_dir setting found, not writing email but returning success");
            return true;
        }

        $data = "To: ".$to."\n";
        $data .= "Subject: ".$subject."\n";
        $data .= $headers."\n";
        $data .= "\n\n\n";
        $data .= $body;

        $outputFile = sha1($to.$subject.$body.$headers);
        $handle = fopen($outputDir."/".$outputFile.".txt", "w");
        fwrite($handle, $data);
        fclose($handle);
        Log::debug("Written test email to file [".$outputDir."/".$outputFile.".txt");
        return true;
    }
}
