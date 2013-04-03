<?php namespace Ekotechnology\Balanced\Requests;

use Ekotechnology\Balanced\Representations\Customer;
use Ekotechnology\Balanced\Representations\Merchant;
use Ekotechnology\Balanced\Representations\Debit;

use Ekotechnology\Balanced\Exceptions\InvalidArgument;
use Ekotechnology\Balanced\Exceptions\UriNotSpecified;

class Account {

	var $params = array();

	function __construct($representation) {
		$this->representation = $representation;
	}

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
	 * Adds on_behalf_of_uri parameter to request
	 * @param  Merchant $merchant Merchant to process the transaction for
	 * @return self
	 */
	function onBehalfOf(Merchant $merchant) {
		$this->params['on_behalf_of_uri'] = $merchant->uri;
		return $this;
	}

	/**
	 * Create an account with Customer data
	 * @param  Customer $customer
	 * @return Account
	 */
	function create(Customer $customer) {
		$reqParams = array_merge($this->params, $customer->getItems());
		$request = $this->representation->instance->client->post('marketplaces/{MARKETPLACE_ID}/accounts', null, $reqParams);
		$response = $request->send();
		$this->representation->fill($response->json());
		return $this->representation;
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
			$type = explode("\\", get_class($src));
			$srcType = end($type);

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
				// var_dump(array_merge($this->params, $reqParams));
				
			}
			else {
				throw new InvalidArgument(end($type) . ' is not an acceptable charge source.');
			}
		}
		else {
			throw new InvalidArgument("Payment Source must be Bank or Card object. " . gettype($src) . " given.");
		}
	}
}