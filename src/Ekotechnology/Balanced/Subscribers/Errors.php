<?php namespace Ekotechnology\Balanced\Subscribers;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;

class Errors implements EventSubscriberInterface {
	private $config = array();

	public function __construct() {
		// $this->config = $config;
	}

	public static function getSubscribedEvents() {
		return array(
			'request.error' => array('requestError', 100)
		);
	}

	public function requestError(Event $event) {
		$request = $event['request'];
	}
}