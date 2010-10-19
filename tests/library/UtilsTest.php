<?php
class UtilsTest extends PHPUnit_Framework_TestCase {

	public function testFuzzyTimeWithIdenticalTimestamps() {
		$this->assertEquals("Just now", Utils::fuzzyTime(time(), time()));
		
		$this->assertEquals("Just now", Utils::fuzzyTime(time()));
		
		$this->assertEquals("Just now", Utils::fuzzyTime(date("Y-m-d H:i:s"), time()));
		
		$this->assertEquals("Just now", Utils::fuzzyTime(date("Y-m-d H:i:s")));
		
		$this->assertEquals("Just now", Utils::fuzzyTime(time(), date("Y-m-d H:i:s")));
		
		$this->assertEquals("Just now", Utils::fuzzyTime(date("Y-m-d H:i:s"), date("Y-m-d H:i:s")));
	}
	
	public function testFuzzyTimeWithThirtySecondGap() {
		$from = time();
		$to = $from + 30;
		$this->assertEquals("About a minute ago", Utils::fuzzyTime($from, $to));
	}

    public function testFuzzyTimeWithLargeGaps() {
        $this->assertEquals("25 days ago", Utils::fuzzyTime(date("Y-m-d H:i:s", strtotime("-25 days"))));
        $this->assertEquals("Yesterday", Utils::fuzzyTime(date("Y-m-d H:i:s", strtotime("-1 day"))));
    }

    public function testFromCamelCase() {
        $this->assertEquals("camel_case", Utils::fromCamelCase("CamelCase"));
    }
}
