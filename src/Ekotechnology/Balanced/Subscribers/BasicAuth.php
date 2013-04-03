<?php namespace Ekotechnology\Balanced\Subscribers;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;

class BasicAuth implements EventSubscriberInterface {

	private $config = array();

	public function __construct(Array $config) {
		$this->config = $config;
	}

	public static function getSubscribedEvents() {
		return array(
			'request.before_send' => array('requestBeforeSend', 100)
		);
	}

	public function requestBeforeSend(Event $event) {
		$event['request']->setHeader('Authorization', 'Basic ' . base64_encode($this->config['API_KEY'] . ':'));
	}
}