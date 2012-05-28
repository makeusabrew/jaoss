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
        $this->assertTrue(Validate::email("FOO@BAR.COM"));

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

    public function testDateTime() {
        $this->assertTrue(Validate::dateTime("01/04/2010 00:00:00"));
        $this->assertTrue(Validate::dateTime("01/04/10 00:00:00"));
        $this->assertTrue(Validate::dateTime("01/04/2010 00:00"));
        $this->assertTrue(Validate::dateTime("01/04/10 00:00"));

        $this->assertFalse(Validate::dateTime("01/04/0 00:00:00"));
        $this->assertFalse(Validate::dateTime("01/04/2010 00:00:"));
        $this->assertFalse(Validate::dateTime("01/04/10 00:00:"));
        $this->assertFalse(Validate::dateTime("01/04/10 00"));
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

    public function testPostcode() {
        $this->assertTrue(Validate::postcode("LS1 3BR"));
        $this->assertTrue(Validate::postcode("ls1 4ap"));
        $this->assertTrue(Validate::postcode("GIR 0AA"));

        $this->assertFalse(Validate::postcode("LS1"));
    }

    public function testUnsigned() {
        $this->assertTrue(Validate::unsigned(0));
        $this->assertTrue(Validate::unsigned("0"));
        $this->assertTrue(Validate::unsigned(1));
        $this->assertTrue(Validate::unsigned(10));

        $this->assertFalse(Validate::unsigned(-1));
        $this->assertFalse(Validate::unsigned("-1"));
        $this->assertFalse(Validate::unsigned(-123.45));
    }

    public function testMatchOption() {
        $options = array(
            'options' => array(
                'foo' => 'Foo',
                'bar' => 'Bar',
                'baz' => 'Baz',
            ),
        );

        // match keys, not values
        $this->assertFalse(Validate::matchOption('Foo', $options));
        $this->assertFalse(Validate::matchOption('invalid', $options));

        $this->assertTrue(Validate::matchOption('foo', $options));
        $this->assertTrue(Validate::matchOption('bar', $options));
        $this->assertTrue(Validate::matchOption('baz', $options));
    }

    public function testMatchCheckboxOptions() {
        $options = array(
            'options' => array(
                'foo' => 'Foo',
                'bar' => 'Bar',
                'baz' => 'Baz',
            ),
        );

        $this->assertTrue(Validate::matchCheckboxOptions(array(
            'foo' => 'On',
        ), $options));

        $this->assertTrue(Validate::matchCheckboxOptions(array(
            'foo' => 'On',
            'bar' => 'On',
        ), $options));

        $this->assertTrue(Validate::matchCheckboxOptions(array(
            'foo' => 'On',
            'bar' => 'On',
            'baz' => 'On',
        ), $options));

        $this->assertFalse(Validate::matchCheckboxOptions(array(
            'foo' => 'On',
            'bar' => 'On',
            'baz' => 'On',
            'invalid' => 'On',
        ), $options));

        $this->assertFalse(Validate::matchCheckboxOptions(array(
            'Foo' => 'On',
        ), $options));

        $this->assertFalse(Validate::matchCheckboxOptions(array(), $options));
    }

    public function testMatchCheckboxOptionsFailsWithStrings() {
        $this->assertFalse(Validate::matchCheckboxOptions("foo", array("foo" => "Foo")));
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
            'foo must be in the format dd/mm/yyyy hh:mm:ss',
            Validate::getMessage('dateTime', array('title' => 'foo'), null)
        );

        $this->assertEquals(
            'foo is not valid',
            Validate::getMessage('unknown', array('title' => 'foo'), null)
        );

        $this->assertEquals(
            'foo does not meet the minimum age requirement of 18',
            Validate::getMessage('minAge', array('title' => 'foo', 'age' => 18), null)
        );

        $this->assertEquals(
            'foo is not a valid postcode',
            Validate::getMessage('postcode', array('title' => 'foo'), null)
        );

        $this->assertEquals(
            'foo does not match one of the available options',
            Validate::getMessage('matchOption', array('title' => 'foo'), null)
        );
        
        $this->assertEquals(
            'one or more of the options chosen for foo are not valid',
            Validate::getMessage('matchCheckboxOptions', array('title' => 'foo'), null)
        );

        $this->assertEquals(
            'foo must be zero or greater',
            Validate::getMessage('unsigned', array('title' => 'foo'), null)
        );
    }

    public function testDateWithValidFormatButInvalidActualDate() {
        // blatant
        $this->assertFalse(Validate::date("99/04/10"));
        $this->assertFalse(Validate::date("10/13/10"));

        // more subtle
        $this->assertFalse(Validate::date("31/09/10"));
    }

    public function testDateWithLeapYears() {
        // non leap year
        $this->assertFalse(Validate::date("29/02/10"));
        // leap year
        $this->assertTrue(Validate::date("29/02/12"));
    }

    public function testDateTimeWithValidFormatButInvalidActualDate() {
        // blatant
        $this->assertFalse(Validate::dateTime("99/04/10 10:00"));
        $this->assertFalse(Validate::dateTime("10/13/10 12:34:56"));

        // more subtle
        $this->assertFalse(Validate::dateTime("31/09/10 09:00:00"));

        // non leap year
        $this->assertFalse(Validate::dateTime("29/02/10 09:00:00"));
    }
}
