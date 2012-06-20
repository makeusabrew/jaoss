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
            "id,created,updated,myVariable,anotherVariable",
            $this->table->getColumnString()
        );
    }

    public function testGetColumnStringWithPrefix() {
        $this->assertEquals(
            "t.id,t.created,t.updated,t.myVariable,t.anotherVariable",
            $this->table->getColumnString("t")
        );
    }

    public function testGetColumnStringWithFieldPrefix() {
        $this->assertEquals(
            "id AS t_id,created AS t_created,updated AS t_updated,myVariable AS t_myVariable,anotherVariable AS t_anotherVariable",
            $this->table->getColumnString(null, "t_")
        );
    }

    public function testGetColumnStringWithPrefixAndFieldPrefix() {
        $this->assertEquals(
            "t.id AS t_id,t.created AS t_created,t.updated AS t_updated,t.myVariable AS t_myVariable,t.anotherVariable AS t_anotherVariable",
            $this->table->getColumnString("t", "t_")
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
            "id,updated,myVariable,anotherVariable",
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
            "id,created,myVariable,anotherVariable",
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

    public function testGetColumnsArray() {
        $this->assertEquals(
            array("id", "created", "updated", "myVariable", "anotherVariable"),
            $this->table->getColumnsArray()
        );
    }

    public function testGetObjectNameThrowsExceptionIfObjectDoesNotExist() {
        $this->table = $this->getMockForAbstractClass('Table', array(), '', true, true, true, array('getClassName'));
        $this->table->expects($this->any())
             ->method('getClassName')
             ->will($this->returnValue("SomeObjects"));

        try {
            $this->table->getObjectName();
        } catch (CoreException $e) {
            $this->assertEquals("Object class does not exist: SomeObject", $e->getMessage());
            return;
        }

        $this->fail("Expected exception not raised");
    }

    public function testGetTable() {
        $this->table = $this->getMockForAbstractClass('Table', array(), '', true, true, true, array('getClassName'));
        $this->table->expects($this->any())
             ->method('getClassName')
             ->will($this->returnValue("SomeObjects"));

        $this->assertEquals("some_objects", $this->table->getTable());
    }

    public function testGetFullColumns() {
        $this->assertEquals(array(
            'id' => array(
                'type' => 'primary_key',
            ),
            'created' => array(
                'type' => 'datetime',
            ),
            'updated' => array(
                'type' => 'datetime',
            ),
            'myVariable' => array(
                'type' => 'text',
            ),
            'anotherVariable' => array(
                'type' => 'number',
                'required' => true,
            ),
        ), $this->table->getFullColumns());
    }
}
