<?php

// @todo change to a proper mock object when we next have
// internet to google how :)
class TestObject extends Object {
    //
}

// ew. @todo remove!!
class TestObjects extends Table {
    protected $meta = array(
        'columns' => array(
            'myVariable' => array(
                'type' => 'text',
            ),
            'anotherVariable' => array(
                'type' => 'number',
                'required' => true,
            ),
        ),
    );
}

class ObjectTest extends PHPUnit_Framework_TestCase {

    protected $object;

    public function setUp() {
        $this->object = new TestObject();
    }
    
    public function testSimpleSetAndGet() {
        $this->assertNull($this->object->myVariable);

        $this->object->myVariable = "fooBar";

        $this->assertEquals("fooBar", $this->object->myVariable);
    }

    public function testGetValuesWithNoData() {
        $this->assertEquals(array(
            'id' => null,
        ), $this->object->getValues());
    }

    public function testGetValuesWithSimpleData() {
        $this->object->myVariable = "fooBar";
        $this->object->anotherVariable = 123;

        $this->assertEquals(array(
            'id' => null,
            'myVariable' => 'fooBar',
            'anotherVariable' => 123,
        ), $this->object->getValues());
    }

    public function testSetValuesReturnsFalseWithIncompleteData() {
        $this->assertFalse(
            $this->object->setValues(array(
                'myVariable' => 'foo',
            ))
        );
    }

    public function testSetValuesReturnsTrueWithMinimumRequiredData() {
        $this->assertTrue(
            $this->object->setValues(array(
                'anotherVariable' => 123,
            ))
        );
    }

    public function testSetValuesReturnsTrueWithCompleteData() {
        $this->assertTrue(
            $this->object->setValues(array(
                'myVariable' => 'foo',
                'anotherVariable' => 123,
            ))
        );
    }

    public function testSetValuesIgnoresUnknownFieldNames() {
        $this->object->setValues(array(
            'myVariable' => 'foo',
            'anotherVariable' => 123,
            'ignoredVar' => 'bar',
            'test' => 'baz',
        ));

        $this->assertEquals('foo', $this->object->myVariable);
        $this->assertEquals(123, $this->object->anotherVariable);
        $this->assertNull($this->object->ignoredVar);
        $this->assertNull($this->object->test);
    }

    public function testUpdateValuesWithNoPartialFlag() {
        $this->object->setValues(array(
            'myVariable' => 'foo',
            'anotherVariable' => 123,
        ));

        $result = $this->object->updateValues(array(
            'myVariable' => 'bar',
        ));

        $this->assertTrue($result);

        $this->assertEquals('bar', $this->object->myVariable);
    }

    // can't remember the use case for the parial flag at the mo :S
    // need to write a proper test for it when I do!
    //public function testUpdateValuesWithPartialFlag() {}

}
