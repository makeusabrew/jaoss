<?php
class EmailTest extends PHPUnit_Framework_TestCase {

    public function testGetToAsStringStartsEmpty() {
        $email = Email::factory();
        $this->assertType("string", $email->getToAsString());
        $this->assertEquals("", $email->getToAsString());
    }

    public function testGetToAsStringWhenToIsString() {
        $email = Email::factory();
        $email->setTo("foo@bar.com");
        $this->assertEquals("foo@bar.com", $email->getToAsString());
    }

    public function testGetToAsStringWhenToIsArray() {
        $email = Email::factory();
        $email->setTo(array("foo@bar.com"));
        $this->assertEquals("foo@bar.com", $email->getToAsString());

        $email->setTo(array("foo@bar.com", "bar@baz.com"));
        $this->assertEquals("foo@bar.com,bar@baz.com", $email->getToAsString());
    }

    public function testSetAndGetFrom() {
        $email = Email::factory();
        $this->assertNull($email->getFrom());

        $email->setFrom("foo@bar.com");
        $this->assertEquals("foo@bar.com", $email->getFrom());
    }

    public function testSetAndGetSubject() {
        $email = Email::factory();
        $this->assertNull($email->getSubject());

        $email->setSubject("My Test Subject");
        $this->assertEquals("My Test Subject", $email->getSubject());
    }

    public function testSetAndGetBody() {
        $email = Email::factory();
        $this->assertNull($email->getBody());

        $email->setBody("My Test Body");
        $this->assertEquals("My Test Body", $email->getBody());
    }

    public function testSendReturnValue() {
        $email = Email::factory();
        $this->assertFalse($email->send());
        $email->setTo("foo@bar.com");
        $this->assertFalse($email->send());
        $email->setFrom("an@address.com");
        $this->assertFalse($email->send());
        $email->setBody("This is a test body");
        $this->assertFalse($email->send());
        $email->setSubject("This is a test header");
        $this->assertTrue($email->send());
    }
}
