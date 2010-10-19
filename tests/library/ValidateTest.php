<?php
require_once("validate.php");

class ValidateTest extends PHPUnit_Framework_TestCase {
    
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
