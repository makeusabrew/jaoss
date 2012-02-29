<?php
class TableTest extends PHPUnit_Framework_TestCase {
    protected $table;

    public function setUp() {
        $this->table = $this->getMockForAbstractClass('Table', array(), '', true, true, true, array('getColumns'));
        $this->table->expects($this->any())
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

    public function testGetColumnStringNoPrefix() {
        $this->assertEquals(
            "id,myVariable,anotherVariable,created,updated",
            $this->table->getColumnString()
        );
    }

    public function testGetColumnStringWithPrefix() {
        $this->assertEquals(
            "t.id,t.myVariable,t.anotherVariable,t.created,t.updated",
            $this->table->getColumnString("t")
        );
    }

    public function testGetColumnStringNoCreated() {
        $this->table = $this->getMockForAbstractClass('Table', array(), '', true, true, true, array('getColumns', 'shouldStoreCreated'));
        $this->table->expects($this->any())
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
        $this->table->expects($this->any())
             ->method('shouldStoreCreated')
             ->will($this->returnValue('false'));

        $this->assertEquals(
            "id,myVariable,anotherVariable,updated",
            $this->table->getColumnString()
        );
    }

    public function testGetColumnStringNoUpdated() {
        $this->table = $this->getMockForAbstractClass('Table', array(), '', true, true, true, array('getColumns', 'shouldStoreUpdated'));
        $this->table->expects($this->any())
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
        $this->table->expects($this->any())
             ->method('shouldStoreUpdated')
             ->will($this->returnValue('false'));

        $this->assertEquals(
            "id,myVariable,anotherVariable,created",
            $this->table->getColumnString()
        );
    }

    public function testGetColumnStringNoCreatedOrUpdated() {
        $this->table = $this->getMockForAbstractClass('Table', array(), '', true, true, true, array('getColumns', 'shouldStoreCreated', 'shouldStoreUpdated'));
        $this->table->expects($this->any())
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

        $this->table->expects($this->any())
             ->method('shouldStoreCreated')
             ->will($this->returnValue('false'));

        $this->table->expects($this->any())
             ->method('shouldStoreUpdated')
             ->will($this->returnValue('false'));

        $this->assertEquals(
            "id,myVariable,anotherVariable",
            $this->table->getColumnString()
        );
    }
}
