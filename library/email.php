<?php
class Email {
    protected $headers = array();
    protected $to;
    protected $subject;
    protected $from;
    protected $body;
    protected $handler;

    public static function factory($handler = null) {
        $email = new Email();
        if ($handler === null) {
            try {
                $handler = Settings::getValue("email.handler");
            } catch (CoreException $e) {
                $handler = "default";
            }
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
            $handler = get_class($this->handler);
            Log::debug("[".$handler."] sending mail to [".$this->getToAsString()."], from [".$this->getFrom()."], subject [".$this->getSubject()."], body length [".strlen($this->getBody())."]");
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

    public function getHandlerName() {
        return $this->handler->getName();
    }

    public function getLastIdentifier() {
        return $this->handler->getLastIdentifier();
    }
}

interface IEmailHandler {
    public function send($to, $subject, $body, $headers, $from);
    public function getName();
    public function getLastIdentifier();
}

abstract class EmailHandler {

    public static function factory($mode) {
        if ($mode == "autodetect") {
            if (php_sapi_name() == "cli") {
                $mode = "test";
            } else {
                // test, apache and autodetect = DB please
                $mode = "db";
            }
        }

        $prefix = ucfirst(strtolower($mode));
        if (class_exists($prefix."EmailHandler")) {
            $class = $prefix."EmailHandler";
            return new $class;
        }
        throw new CoreException("Email Handler [".$prefix."EmailHandler] does not exist");
    }
}

class DefaultEmailHandler implements IEmailHandler {
    public function send($to, $subject, $body, $headers, $from) {
        return mail($to, $subject, $body, $headers);
    }

    public function getName() {
        return "Default";
    }

    public function getLastIdentifier() {
        return null;
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
        return true;
    }

    public function getName() {
        return "TestStatic";
    }

    public function getLastIdentifier() {
        return count(self::$sentEmails);
    }
}

class DbEmailHandler implements IEmailHandler {
    protected static $sinceId = null;
    protected static $lastIdentifier = null;

    public static function resetSentEmails() {
        $db = Db::getInstance();
        $result = $db->query("SELECT `id` FROM `test_emails` ORDER BY `id` DESC LIMIT 1")->fetch();
        self::$sinceId = $result[0];
    }

    public function send($to, $subject, $body, $headers, $from) {
        $db = Db::getInstance();
        /*
        $db->exec("CREATE TABLE IF NOT EXISTS test_emails (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `to` varchar(255) NOT NULL,
            `subject` varchar(255) NOT NULL,
            `body` TEXT NOT NULL,
            `headers` TEXT NOT NULL,
            `from` varchar(255) NOT NULL,
            `created` DATETIME NOT NULL,
            PRIMARY KEY(`id`))");
        */
        $sth = $db->prepare("INSERT INTO test_emails (`to`,`subject`,`body`,`headers`,`from`, `created`) VALUES(?, ?, ?, ?, ?, ?)");
        $result = $sth->execute(array($to, $subject, $body, $headers, $from, Utils::getDate("Y-m-d H:i:s")));

        self::$lastIdentifier = $db->lastInsertId();

        return $result;
    }

    public static function getSentEmails() {
        $db = Db::getInstance();
        $params = array();
        $query = "SELECT * FROM `test_emails`";
        if (self::$sinceId !== null) {
            $query .= " WHERE `id` > ?";
            $params[] = self::$sinceId;
        }
        $query .= " ORDER BY `id` ASC";
        $sth = $db->prepare($query);
        $sth->execute($params);
        return $sth->fetchAll();
    }

    public function getName() {
        return "TestDb";
    }

    public function getLastIdentifier() {
        return self::$lastIdentifier;
    }
}
