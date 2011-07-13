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
        $this->assertTrue(Validate::email("foo@bar.name"));

        $this->assertFalse(Validate::email("address"));
        $this->assertFalse(Validate::email("address@"));
    }

    public function testMinLength() {
        $this->assertTrue(Validate::minLength("somestring"));
        $this->assertTrue(Validate::minLength("somestring", array("length" => "4")));
        $this->assertTrue(Validate::minLength("somestring", array("length" => "10")));
        $this->assertTrue(Validate::minLength("somestring", array("minLength" => "10")));

        $this->assertFalse(Validate::minLength("somestring", array("length" => "11")));
        $this->assertFalse(Validate::minLength("somestring", array("minLength" => "11")));
    }

    public function testMaxLength() {
        $this->assertFalse(Validate::maxLength("anotherstring"));
        $this->assertFalse(Validate::maxLength("anotherstring", array("length" => "12")));
        $this->assertFalse(Validate::maxLength("anotherstring", array("maxLength" => "12")));

        $this->assertTrue(Validate::maxLength("anotherstring", array("length" => "13")));
        $this->assertTrue(Validate::maxLength("anotherstring", array("maxLength" => "13")));
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

    public function testMinAge() {
        $this->assertTrue(Validate::minAge('02/06/1993', array(
            'age'       => '18',
            'target' => '02/06/2011',
        )), 'Age is at least 18');
        $this->assertTrue(Validate::minAge('01/06/1993', array(
            'age'       => '18',
            'target' => '02/06/2011',
        )), 'Age is at least 18');
        $this->assertFalse(Validate::minAge('03/06/1993', array(
            'age'       => '18',
            'target' => '02/06/2011',
        )), 'Age is not 18');
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

    public function testGetMessage() {
        $this->assertEquals(
            'foo is not a valid email address',
            Validate::getMessage('email', array('title' => 'foo'), null)
        );

        $this->assertEquals(
            'foo is required',
            Validate::getMessage('required', array('title' => 'foo'), null)
        );

        $this->assertEquals(
            'foo must be at least 8 characters long',
            Validate::getMessage('minLength', array('title' => 'foo', 'length' => 8), null)
        );

        $this->assertEquals(
            'foo must be at least 8 characters long',
            Validate::getMessage('minLength', array('title' => 'foo', 'minLength' => 8), null)
        );

        $this->assertEquals(
            'foo must be no more than 8 characters long',
            Validate::getMessage('maxLength', array('title' => 'foo', 'length' => 8), null)
        );

        $this->assertEquals(
            'foo must be no more than 8 characters long',
            Validate::getMessage('maxLength', array('title' => 'foo', 'maxLength' => 8), null)
        );

        $this->assertEquals(
            'the two foos do not match',
            Validate::getMessage('match', array('title' => 'foo'), null)
        );

        $this->assertEquals(
            'this foo is already in use',
            Validate::getMessage('unique', array('title' => 'foo'), null)
        );

        $this->assertEquals(
            'foo must contain only numbers and spaces',
            Validate::getMessage('numbersSpaces', array('title' => 'foo'), null)
        );

        $this->assertEquals(
            'foo must contain only numbers',
            Validate::getMessage('numbers', array('title' => 'foo'), null)
        );

        $this->assertEquals(
            'foo must be in the format dd/mm/yyyy',
            Validate::getMessage('date', array('title' => 'foo'), null)
        );

        $this->assertEquals(
            'foo is not valid',
            Validate::getMessage('unknown', array('title' => 'foo'), null)
        );

        $this->assertEquals(
            'foo does not meet the minimum age requirement of 18',
            Validate::getMessage('minAge', array('title' => 'foo', 'age' => 18), null)
        );
    }
}
