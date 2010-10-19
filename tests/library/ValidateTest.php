<?php
class ValidateTest extends PHPUnit_Framework_TestCase {
    
    public function testRequiredWithValidInput() {
        $this->assertTrue(Validate::required("Foo Bar"));
    }

    public function testRequiredWithInvalidInput() {
        $this->assertFalse(Validate::required("   "));
    }

    public function testEmail() {
        $this->assertTrue(Validate::email("foo@bar.com"));
        $this->assertTrue(Validate::email("some.address@anotherdomain.com"));
        $this->assertTrue(Validate::email("address@somedomain.co.uk"));

        $this->assertFalse(Validate::email("address"));
        $this->assertFalse(Validate::email("address@"));
    }

    public function testMinLength() {
        $this->assertTrue(Validate::minLength("somestring"));
        $this->assertTrue(Validate::minLength("somestring", array("length" => "4")));
        $this->assertTrue(Validate::minLength("somestring", array("length" => "10")));

        $this->assertFalse(Validate::minLength("somestring", array("length" => "11")));
    }

    public function testMatch() {
        $this->assertTrue(Validate::match("my string", array("confirm" => "my string")));

        $this->assertFalse(Validate::match("my string", array("confirm" => "some string")));
    }

    public function testDate() {
        $this->assertTrue(Validate::date("01/04/2010"));
        $this->assertTrue(Validate::date("01/04/10"));

        $this->assertFalse(Validate::date("1/4/10"));
        $this->assertFalse(Validate::date("10/04/1"));
        $this->assertFalse(Validate::date("10/04/100"));
    }

    public function testNumbersSpacesWithValidInput() {
        $this->assertTrue(Validate::numbersSpaces("1235", array()));
        $this->assertTrue(Validate::numbersSpaces("12 3 5", array()));
    }

    public function testNumbersSpacesWithInvalidInput() {
        $this->assertFalse(Validate::numbersSpaces("   12 3 5", array()));
        $this->assertFalse(Validate::numbersSpaces("1234 ", array()));
        $this->assertFalse(Validate::numbersSpaces("123f65", array()));
    }
}
