<?php
class FlashMessenger {
	
	public static function addMessage($str) {
		$messages = Session::getInstance()->flash_messages;
		$messages[] = $str;
		Session::getInstance()->flash_messages = $messages;
	}
	
	public static function getMessages() {
		$messages = Session::getInstance()->flash_messages;
		unset(Session::getInstance()->flash_messages);
		return $messages;
	}
}
