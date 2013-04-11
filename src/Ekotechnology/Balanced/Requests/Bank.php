<?php namespace Ekotechnology\Balanced\Requests;

use Ekotechnology\Balanced\Requests\ParamsTrait;
use Ekotechnology\Balanced\Requests\Traits\GetTrait;

class Bank {
	use ParamsTrait;

	/**
	 * Attempts to tokenize the given Bank Account details
	 * (THIS SHOULD PROBABLY NOT BE USED IN PRODUCTION, USE
	 * BALANCED.JS INSTEAD FOR SECURITY PURPOSES)
	 * @return this
	 */
	function tokenize() {
		$request = $this->representation->instance->client->post('bank_accounts', array(), $this->representation->getItems());
		return $this->representation->replace($request->send()->json());
	}

	/**
	 * Get an account by URI
	 * @param  string $uri [description]
	 * @return Card
	 */
	function get($uri="") {
		$request = $this->representation->instance->client->get($uri);
		$response = $request->send();
		$this->representation->fill($response->json());
		return $this->representation;
	}
}