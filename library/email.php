<?php
class Email {
    protected $headers = array();
    protected $to;
    protected $subject;
    protected $from;
    protected $body;

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
            Log::debug("sending mail to [".$this->to."], from [".$this->from."], subject [".$this->subject."], body length [".strlen($this->body)."]");
            return mail($this->to, $this->subject, $this->body, $this->getHeadersAsString());
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
}
