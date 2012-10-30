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

    public function testPassingAssignParamAssignstoCorrectVar() {
        $tpl = new DummyTemplate();

        smarty_function_validation_regex(
        array(
            "type" => "date",
            "assign" => "foo",
        ), $tpl);

        $this->assertEquals(
            "(\d{2})/(\d{2})/(\d{2}|\d{4})",
            $tpl->getVar("foo")
        );
    }
}

class DummyTemplate {
    protected $vars = array();
    public function assign($var, $value) {
        $this->vars[$var] = $value;
    }

    public function getVar($var) {
        return $this->vars[$var];
    }
}
