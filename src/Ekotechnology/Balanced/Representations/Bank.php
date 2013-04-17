<?php namespace Ekotechnology\Balanced\Representations;

class Bank {
	use RepresentationTrait;

	function checking() {
		$this->type = "checking";
		return $this;
	}

	function savings() {
		$this->type = "savings";
		return $this;
	}
}