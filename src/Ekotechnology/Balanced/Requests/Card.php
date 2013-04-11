<?php namespace Ekotechnology\Balanced\Requests;
use Ekotechnology\Balanced\Requests\Traits\GetTrait;

class Card {
	use GetTrait;	
	use ParamsTrait;
	
	/**
	 * Attempts to tokenize the card from the representation
  	 * (THIS SHOULD PROBABLY NOT BE USED IN PRODUCTION, USE
	 * BALANCED.JS INSTEAD FOR SECURITY PURPOSES)
	 * @return Card
	 */
	function tokenize() {
		$request = $this->representation->instance->client->post('marketplaces/{MARKETPLACE_ID}/cards', null, $this->representation->getItems());
		$response = $request->send();
		$this->representation->fill($response->json());
		return $this->representation;
	}
}