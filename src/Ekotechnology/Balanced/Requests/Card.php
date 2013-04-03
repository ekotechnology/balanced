<?php namespace Ekotechnology\Balanced\Requests;

class Card {
	var $params = array();

	function __construct($representation) {
		$this->representation = $representation;
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

	function tokenize() {
		$request = $this->representation->instance->client->post('marketplaces/{MARKETPLACE_ID}/cards', null, $this->representation->getItems());
		$response = $request->send();
		$this->representation->fill($response->json());
		return $this->representation;
	}
}