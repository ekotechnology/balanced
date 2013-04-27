<?php namespace Ekotechnology\Balanced\Requests\Traits;

trait GetTrait {

	function get($uri="") {
		if ($this->representation->uri) {
			$uri = $this->representation->uri;
		}
		elseif (empty($uri)) {
			throw new InvalidArgument("URI not set for object retrieval, and none was given to get method.");
		}

		$request = $this->representation->instance->client->get($uri)->send();
		$response = $request->send();
		$this->representation->fill($response->json());
		return $this->representation;
	}
}