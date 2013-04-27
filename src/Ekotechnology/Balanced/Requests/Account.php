<?php namespace Ekotechnology\Balanced\Requests;

use Ekotechnology\Balanced\Requests\Traits\GetTrait;

use Ekotechnology\Balanced\Representations\Customer;
use Ekotechnology\Balanced\Representations\Merchant;
use Ekotechnology\Balanced\Representations\Debit;

use Ekotechnology\Balanced\Exceptions\InvalidArgument;
use Ekotechnology\Balanced\Exceptions\UriNotSpecified;

class Account {
	use ParamsTrait;

	var $underwrite;

	/**
	 * Adds a card URI to the request
	 * @param  string $card Card URI
	 * @return self
	 */
	function withCard($card) {
		$this->params['card_uri'] = $card;
		return $this;
	}

	/**
	 * Add an account URI to the request
	 * @param  string $account Account URI
	 * @return self
	 */
	function withAccount($account) {
		$this->params['bank_account_uri'] = $account;
		return $this;
	}

	/**
	 * Adds on_behalf_of_uri parameter to request
	 * @param  Merchant $merchant Merchant to process the transaction for
	 * @return self
	 */
	function onBehalfOf(Merchant $merchant) {
		$this->params['on_behalf_of_uri'] = $merchant->uri;
		return $this;
	}

	/**
	 * Create an account with data
	 * @param  Buyer|Business|Person $customer
	 * @return Account
	 */
	function create($customer) {
		// Make sure the input is an object of some kind
		if (is_object($customer)) {
			// It is an object, now determine the type
			$srcType = $this->objectType($customer);
			switch ($srcType) {
				case 'Business':
					$required = ['name', 'phone_number', 'tax_id', 'dob', 'street_address', 'postal_code', 'person', 'person.name', 'person.dob', 'person.street_address', 'person.postal_code', 'person.tax_id'];
				break;

				case 'Person':
					$required = ['name', 'email_address', 'tax_id', 'street_address', 'postal_code', 'dob'];
				break;

				case 'Buyer':
					$required = ['name', 'email_address'];

				break;

				default:
					// We didn't get a Buyer, Business, or Person.
					// Throw an error saying as much.
					throw new InvalidArgument("Customer data must be of object type Buyer, Person, or Business. "  . $srcType . " given.");
			}

			foreach ($required as $val) {
				if (strstr($val, '.')) {
					$explode = explode('.', $val);
					$parent = $explode[0];
					$key = $explode[1];

					if (!array_key_exists($key, $customer->getItems()[$parent])) {
						throw new InvalidArgument("$parent.$key field required on " . $srcType . " object.");
					}
				}
				else {
					if (!array_key_exists($val, $customer->getItems())) {
						throw new InvalidArgument("$val field required on " . $srcType . " object.");
					}
				}
			}

			$this->params['name'] = $customer->name;
			$this->params['email_address'] = $customer->email_address;

			if ($srcType == 'Business' || $srcType == "Person") {
				$this->params['merchant'] = $customer->getItems();
				$this->params['merchant']['type'] = ($srcType == 'Business') ? 'business' : 'person';
			}

			$this->params = array_merge($this->params, $customer->getItems());
			$request = $this->representation->instance->client->post('marketplaces/{MARKETPLACE_ID}/accounts', null, $this->params);
			$response = $request->send();
			$this->params = [];
			$this->representation->fill($response->json());
			return $this->representation;
		}
		else {
			throw new InvalidArgument("Customer data must be of object type Buyer, Person or Business.");
		}
	}

	/**
	 * Get an Account by URI
	 * @param  string $uri [description]
	 * @return Account
	 */
	function get($uri="") {
		$request = $this->representation->instance->client->get($uri);
		$response = $request->send();
		$this->representation->fill($response->json());
		return $this->representation;
	}

	/**
	 * Charge a Card or Bank on the given Account
	 * @param  Card|Bank $src     Card or Bank Object (must at least contain the uri)
	 * @param  int       $amount  Charge amount in minor denominations (cents)
	 * @return Transaction
	 */
	function charge($src, $amount, $statement="", $description="", $meta=array()) {
		if (!$this->representation->uri) {
			throw new UriNotSpecified("Account URI to charge not specified.");
		}
		if (is_object($src)) {
			$srcType = $this->objectType($src);

			if ($srcType == 'Bank' || $srcType == 'Card') {
				if (!$src->uri) {
					throw new InvalidArgument("URI Missing for charge source of type " . $srcType);
				}

				$reqParams = array(
					'amount' => (int) $amount,
					'appears_on_statement_as' => $statement,
					'meta' => $meta,
					'description' => $description,
					'source_uri' => $src->uri,
				);
				$request = $this->representation->instance->client->post($this->representation->uri . '/debits', null, array_merge($this->params, $reqParams));
				return new Debit($this->representation->instance, $request->send()->json());
			}
			else {
				throw new InvalidArgument(end($type) . ' is not an acceptable charge source.');
			}
		}
		else {
			throw new InvalidArgument("Payment Source must be Bank or Card object. " . gettype($src) . " given.");
		}
	}

	function save() {
		// $this->representation->instance->client->put('marketplaces/{MARKETPLACE_ID}/accounts/' . $)		
	}

	/**
	 * Build merchant array for an individual
	 * @param  Person|Business  Merchant to Underwrite
	 * @return self
	 */
	function underwrite($customer) {
		$srcType = $this->objectType($customer);
		if ($this->representation->uri) {
			switch ($srcType) {
				case 'Business':
					$required = ['name', 'phone_number', 'tax_id', 'street_address', 'postal_code', 'person', 'person.name', 'person.dob', 'person.street_address', 'person.postal_code', 'person.tax_id'];
				break;

				case 'Person':
					$required = ['name', 'email_address', 'tax_id', 'street_address', 'postal_code', 'dob'];
				break;

				case 'Buyer':
					$required = ['name', 'email_address'];

				break;

				default:
					// We didn't get a Buyer, Business, or Person.
					// Throw an error saying as much.
					throw new InvalidArgument("Customer data must be of object type Buyer, Person, or Business. "  . $srcType . " given.");
			}

			foreach ($required as $val) {
				if (strstr($val, '.')) {
					$explode = explode('.', $val);
					$parent = $explode[0];
					$key = $explode[1];

					if (!array_key_exists($key, $customer->getItems()[$parent])) {
						throw new InvalidArgument("$parent.$key field required on " . $srcType . " object.");
					}
				}
				else {
					if (!array_key_exists($val, $customer->getItems())) {
						throw new InvalidArgument("$val field required on " . $srcType . " object.");
					}
				}
			}

			if ($srcType == 'Business' || $srcType == "Person") {
				$this->params['merchant'] = $customer->getItems();
				$this->params['merchant']['type'] = ($srcType == 'Business') ? 'business' : 'person';
			}

			$request = $this->representation->instance->client->put($this->representation->uri, array('Content-Type' => 'application/json'), json_encode($this->params))->send();
			return $this->representation->replace($request->json());
		}
		else {
			throw new InvalidArgument("Account URI not set to underwrite " . $srcType . '.');
		}
	}

	private function objectType($input) {
		$type = explode("\\", get_class($input));
		return end($type);
	}
}