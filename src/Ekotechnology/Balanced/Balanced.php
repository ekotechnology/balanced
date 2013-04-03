<?php namespace Ekotechnology\Balanced;

use \Guzzle\Http\Client;
use Ekotechnology\Balanced\Subscribers\BasicAuth, Ekotechnology\Balanced\Subscribers\Errors;
use Ekotechnology\Balanced\Representations\Account, Ekotechnology\Balanced\Representations\Card, Ekotechnology\Balanced\Representations\Bank;
use Ekotechnology\Balanced\Representations\Customer, Ekotechnology\Balanced\Representations\Merchant;
use Ekotechnology\Balanced\Exceptions\FactoryTypeUnknown;

class Balanced {

	const VERSION = "0.1.0";
	private $config = array();
	var $client;

	function __construct($config=array()) {
		if (!empty($config)) {
			$this->client = new Client("https://api.balancedpayments.com/v1", array(
				'MARKETPLACE_ID' => $config['MARKETPLACE_ID']
				)
			);
			$this->client->setUserAgent("EKO Technology Balanced PHP Client v" . self::VERSION);
			$this->client->addSubscriber(new BasicAuth($config));
			$this->client->addSubscriber(new Errors());
		}
	}

	function init($config = array()) {
		$this->__construct($config);
	}

	function factory($type) {
		switch (strtolower($type)) {
			case 'account':
				return new Account($this);
			break;
			case 'bank':
				return new Bank($this);
			break;
			case 'card':
				return new Card($this);
			break;
			case 'customer':
				return new Customer($this);
			break;
			case 'merchant':
				return new Merchant($this);
			break;
			default:
				throw new FactoryTypeUnknown("$type is not a known factory type.");
		}
	}
}