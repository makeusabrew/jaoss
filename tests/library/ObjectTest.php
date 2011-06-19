<?php
class ObjectTest extends PHPUnit_Framework_TestCase {

    protected $object;

    public function setUp() {
        $this->object = $this->getMockForAbstractClass('Object', array(), '', true, true, true, array('getColumns'));
        $this->object->expects($this->any())
             ->method('getColumns')
             ->will($this->returnValue(array(
                    'myVariable' => array(
                        'type' => 'text',
                    ),
                    'anotherVariable' => array(
                        'type' => 'number',
                        'required' => true,
                    ),
                )));
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
                'anotherVariable' => 234,
            ))
        );
    }

    public function testSetValuesReturnsTrueWithCompleteData() {
        $this->assertTrue(
            $this->object->setValues(array(
                'myVariable' => 'foo',
                'anotherVariable' => 456,
            ))
        );
    }

    public function testSetValuesIgnoresUnknownFieldNames() {
        $this->object->setValues(array(
            'myVariable' => 'oof',
            'anotherVariable' => 321,
            'ignoredVar' => 'bar',
            'test' => 'baz',
        ));

        $this->assertEquals('oof', $this->object->myVariable);
        $this->assertEquals(321, $this->object->anotherVariable);
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

    public function testUpdateValuesWithPartialFlag() {
        $this->object = $this->getMockForAbstractClass('Object', array(), '', true, true, true, array('getColumns'));
        $this->object->expects($this->any())
             ->method('getColumns')
             ->will($this->returnValue(array(
                'myVariable' => array(
                    'type' => 'text',
                    'required' => true,
                ),
                'anotherVariable' => array(
                    'type' => 'number',
                    'required' => true,
                ),
            )));


        $this->object->setValues(array(
            'myVariable' => '',
            'anotherVariable' => '',
        ));

        $this->assertEquals(2, count($this->object->getErrors()));

        $this->object->updateValues(array(
            'myVariable' => '',
        ));

        $this->assertEquals(2, count($this->object->getErrors()));

        // with the partial flag, we should only process and get errors
        // for variables we update

        $this->object->updateValues(array(
            'myVariable' => '',
        ), true);

        $this->assertEquals(1, count($this->object->getErrors()));
    }

    public function testSetValuesHasErrorWithConfirmFlagButNoConfirmValue() {
        $this->object = $this->getMockForAbstractClass('Object', array(), '', true, true, true, array('getColumns'));
        $this->object->expects($this->any())
             ->method('getColumns')
             ->will($this->returnValue(array(
                'myVariable' => array(
                    'type' => 'text',
                    'confirm' => true,
                ),
            )));

        $this->object->setValues(array(
            'myVariable' => 'foo',
        ));

        $this->assertEquals(array(
            'confirm_myVariable' => 'the two myVariables do not match',
        ), $this->object->getErrors());
    }

    public function testSetValuesHasNoErrorsWithConfirmFlagAndNonMatchingConfirmValue() {
        $this->object = $this->getMockForAbstractClass('Object', array(), '', true, true, true, array('getColumns'));
        $this->object->expects($this->any())
             ->method('getColumns')
             ->will($this->returnValue(array(
                'myVariable' => array(
                    'type' => 'text',
                    'confirm' => true,
                ),
            )));

        $this->object->setValues(array(
            'myVariable' => 'foo',
            'confirm_myVariable' => 'bar',
        ));

        $this->assertEquals(array(
            'confirm_myVariable' => 'the two myVariables do not match',
        ), $this->object->getErrors());
    }

    public function testSetValuesHasNoErrorsWithConfirmFlagAndMatchingConfirmValue() {
        $this->object = $this->getMockForAbstractClass('Object', array(), '', true, true, true, array('getColumns'));
        $this->object->expects($this->any())
             ->method('getColumns')
             ->will($this->returnValue(array(
                'myVariable' => array(
                    'type' => 'text',
                    'confirm' => true,
                ),
            )));

        $this->object->setValues(array(
            'myVariable' => 'foo',
            'confirm_myVariable' => 'foo',
        ));

        $this->assertEquals(array(), $this->object->getErrors());
    }

    public function testSetValuesValidatesSelectedTypes() {
        $this->object = $this->getMockForAbstractClass('Object', array(), '', true, true, true, array('getColumns'));
        $this->object->expects($this->any())
             ->method('getColumns')
             ->will($this->returnValue(array(
                'email' => array(
                    'type' => 'email',
                ),
                'date' => array(
                    'type' => 'date',
                ),
            )));

        $this->object->setValues(array(
            'email' => 'invalid',
            'date' => 'invalid',
        ));

        $this->assertEquals(array(
            'email' => 'email is not a valid email address',
            'date' => 'date must be in the format dd/mm/yyyy',
        ), $this->object->getErrors());
    }

    public function testValidateArrayIsMergedCorrectlyWithOtherValidation() {
        $this->object = $this->getMockForAbstractClass('Object', array(), '', true, true, true, array('getColumns'));
        $this->object->expects($this->any())
             ->method('getColumns')
             ->will($this->returnValue(array(
                'email' => array(
                    'type' => 'email',
                    'validation' => array('minLength', 'maxLength',),
                    'minLength' => 15,
                    'maxLength' => 20,
                ),
            )));

        $this->object->setValues(array(
            'email' => 'invalidFormat',
        ));

        $this->assertEquals(array('email' => 'email is not a valid email address'), $this->object->getErrors());

        $this->object->setValues(array(
            'email' => 'foo@bar.com',
        ));

        $this->assertEquals(array('email' => 'email must be at least 15 characters long'), $this->object->getErrors());

        $this->object->setValues(array(
            'email' => 'toolong@emailaddress.com',
        ));

        $this->assertEquals(array('email' => 'email must be no more than 20 characters long'), $this->object->getErrors());
    }

    public function testValidateKeyWorksAsString() {
        $this->object = $this->getMockForAbstractClass('Object', array(), '', true, true, true, array('getColumns'));
        $this->object->expects($this->any())
             ->method('getColumns')
             ->will($this->returnValue(array(
                'email' => array(
                    'type' => 'email',
                    'validation' => 'minLength',
                    'minLength' => 15,
                ),
            )));

        $this->object->setValues(array(
            'email' => 'foo@bar.com',
        ));

        $this->assertEquals(array('email' => 'email must be at least 15 characters long'), $this->object->getErrors());
    }

    public function testDateTypeProcessing() {
        $this->object = $this->getMockForAbstractClass('Object', array(), '', true, true, true, array('getColumns'));
        $this->object->expects($this->any())
             ->method('getColumns')
             ->will($this->returnValue(array(
                'date' => array(
                    'type' => 'date',
                ),
            )));

        $this->object->setValues(array(
            'date' => 'invalid',
        ));
        $this->assertEquals('invalid', $this->object->date);

        $this->object->setValues(array(
            'date' => '01/04/2010',
        ));
        $this->assertEquals('2010-04-01', $this->object->date);

        $this->object->setValues(array(
            'date' => '01/04/11',
        ));
        $this->assertEquals('2011-04-01', $this->object->date);
    }
    
    public function testPasswordTypeProcessing() {
        $this->object = $this->getMockForAbstractClass('Object', array(), '', true, true, true, array('getColumns'));
        $this->object->expects($this->any())
             ->method('getColumns')
             ->will($this->returnValue(array(
                'password' => array(
                    'type' => 'password',
                ),
                'myVariable' => array(
                    'type' => 'text',
                ),
            )));

        $this->object->setValues(array(
            'password' => '',
            'myVariable' => 'foo',
        ));
        $this->assertEquals('', $this->object->password);

        $this->object->setValues(array(
            'password' => 'bar',
            'myVariable' => 'foo',
        ));
        $this->assertEquals(sha1('bar'), $this->object->password);

        // ensure password gets updated when changed
        $this->object->setValues(array(
            'password' => 'baz',
            'myVariable' => 'foo',
        ));
        $this->assertEquals(sha1('baz'), $this->object->password);

        // but also ensure it doesn't get re-encoded when set again with the same unencoded value 
        $this->object->setValues(array(
            'password' => 'baz',
            'myVariable' => 'foo',
        ));
        $this->assertEquals(sha1('baz'), $this->object->password);
    }

    public function testGetErrorsReturnsEmptyArray() {
        $this->assertEquals(array(), $this->object->getErrors());
    }

    public function testGetChildrenReturnsEmptyArray() {
        $this->assertEquals(array(), $this->object->getChildren());
    }

    public function testGetErrors() {
        $this->object->setValues(array(
            'myVariable' => 'foo',
        ));

        $this->assertEquals(array(
            'anotherVariable' => 'anotherVariable is required',
        ), $this->object->getErrors());
    }

    /*
    public function testGetTable() {
        $this->assertEquals('test_objects', $this->object->getTable());
    }

    public function testGetColumnInfo() {
        $this->assertEquals(array(
            'type' => 'text',
        ), $this->object->getColumnInfo('myVariable'));
    }

    public function testGetHasManyInfo() {
        $this->assertNull($this->object->getHasManyInfo('myVariable'));
    }
    */

    public function testGetUrl() {
        // getUrl by default returns ID which won't be set on a new object...
        $this->assertNull($this->object->getUrl());
    }

    public function testGetFkName() {
        $name = strtolower(get_class($this->object));
        $this->assertEquals($name.'_id', $this->object->getFkName());
    }

    public function testOwnsWithNonObjectArgument() {
        $this->assertFalse($this->object->owns('foo'));
    }
}