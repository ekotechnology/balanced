<?php namespace Ekotechnology\Balanced\Requests;

trait ParamsTrait {
	
	var $params = array();

	function __construct($representation) {
		$this->representation = $representation;
	}
}