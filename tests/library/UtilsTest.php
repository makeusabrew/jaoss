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

    public function testGenerateRandomPasswordLength() {
        $this->assertEquals(8, strlen(Utils::generatePassword(8)));
        $this->assertEquals(1, strlen(Utils::generatePassword(1)));
        $this->assertEquals(100, strlen(Utils::generatePassword(100)));
    }

    public function testGetCurrentTimestampReturnsInteger() {
        $this->assertInternalType('integer', Utils::getTimestamp());
    }

    public function testCurrentTimestampReturnsTimeValueWhenNotOverridden() {
        Utils::setCurrentDate(null);
        $time = time();
        $ts   = Utils::getTimestamp();

        $this->assertSame($ts, $time);
    }

    public function testCurrentTimestampReturnsCorrectValueWhenOverridden() {
        Utils::setCurrentDate("2009-01-01 00:00:00");

        $this->assertEquals(
            1230768000,
            Utils::getTimestamp()
        );
    }

    public function testOlderThan() {
        Utils::setCurrentDate("2009-01-01 12:00:00");

        $this->assertFalse(Utils::olderThan(60, "2009-01-01 13:00:00"));
        $this->assertFalse(Utils::olderThan(60, "2009-01-01 12:00:00"));

        // we define "older than" to literally be >, not >=
        $this->assertFalse(Utils::olderThan(60, "2009-01-01 11:59:00"));

        $this->assertTrue(Utils::olderThan(60, "2009-01-01 11:58:59"));
        $this->assertTrue(Utils::olderThan(60, "2008-01-01 12:00:00"));
    }
}
