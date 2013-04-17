<?php namespace Ekotechnology\Balanced;

use \Guzzle\Http\Client;
use Ekotechnology\Balanced\Subscribers\BasicAuth, Ekotechnology\Balanced\Subscribers\Errors;
use Ekotechnology\Balanced\Representations\Account, Ekotechnology\Balanced\Representations\Card, Ekotechnology\Balanced\Representations\Bank;
use Ekotechnology\Balanced\Representations\Buyer, Ekotechnology\Balanced\Representations\Merchant, Ekotechnology\Balanced\Representations\Business;
use Ekotechnology\Balanced\Representations\Person;
use Ekotechnology\Balanced\Exceptions\FactoryTypeUnknown;

class Balanced {

	const VERSION = "0.2.0";
	private $config = array();
	var $client;

	/**
	 * Fire up a Guzzle Client
	 * @param array $config
	 */
	function __construct($config=array()) {
		if (!empty($config)) {
			$this->config = $config;
			$this->client = new Client("https://api.balancedpayments.com/v1", array(
					'MARKETPLACE_ID' => $config['MARKETPLACE_ID']
				)
			);
			$this->client->setUserAgent("EKO Technology Balanced PHP Client v" . self::VERSION);
			$this->client->addSubscriber(new BasicAuth($config));
			$this->client->addSubscriber(new Errors());
		}
	}

	/**
	 * Initialize or reinitialize the class
	 * @param  array  $config
	 * @return void
	 */
	function init($config = array()) {
		$this->__construct($config);
	}

	/**
	 * Factory method generates different object representations
	 * @param  string $type Class type
	 * @return mixed
	 */
	function factory($type) {
		switch (strtolower($type)) {
			case 'account':
				return new Account($this);
			break;
			case 'bank':
				return new Bank($this);
			break;
			case 'business':
				return new Business($this);
			break;
			case 'buyer':
				return new Buyer($this);
			break;
			case 'card':
				return new Card($this);
			break;
			case 'person':
				return new Person($this);
			break;
			default:
				throw new FactoryTypeUnknown("$type is not a known factory type.");
		}
	}

	function javascript() {
		$output = '<script type="text/javascript" src="https://js.balancedpayments.com/v1/balanced.js"></script>';
		$output .= "\n";
		$output .= '<script type="text/javascript">balanced.init("/v1/marketplaces/' . $this->config['MARKETPLACE_ID'] . '");</script>';
		return $output;
	}
}