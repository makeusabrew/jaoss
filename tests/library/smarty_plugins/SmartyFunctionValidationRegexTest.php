<?php
require_once(JAOSS_ROOT."library/smarty_plugins/function.validation_regex.php");

class SmartyFunctionValidationRegexTest extends PHPUnit_Framework_TestCase {
    //
    public function testValidationRegexTrimsStartAndEndCharacters() {
        $this->assertEquals(
            "#^(\d{2})/(\d{2})/(\d{2}|\d{4})$#",
            Validate::regex("date")
        );

        $this->assertEquals(
            "(\d{2})/(\d{2})/(\d{2}|\d{4})",
            smarty_function_validation_regex(array("type" => "date"), null)
        );
    }
}
