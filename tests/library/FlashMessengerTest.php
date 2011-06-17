<?php
class FlashMessengerTest extends PHPUnit_Framework_TestCase {

    public function tearDown() {
        FlashMessenger::reset();
    }

    public function testFlashMessagesReturnsEmptyArray() {
        $this->assertEquals(array(), FlashMessenger::getMessages());
    }

    public function testAddAndGetSingleMessage() {
        FlashMessenger::addMessage('my test message');

        $this->assertEquals(array(
            'my test message',
        ), FlashMessenger::getMessages());
    }

    public function testAddAndGetMultipleMessages() {
        FlashMessenger::addMessage('my test message');
        FlashMessenger::addMessage('another message');

        $this->assertEquals(array(
            'my test message',
            'another message',
        ), FlashMessenger::getMessages());
    }

    public function testGetMessagesEmptiesMessageArray() {
        FlashMessenger::addMessage('foo');
        FlashMessenger::addMessage('bar');

        $this->assertEquals(2, count(FlashMessenger::getMessages()));

        $this->assertEquals(0, count(FlashMessenger::getMessages()));
    }
}
