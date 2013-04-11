<?php namespace Ekotechnology\Balanced\Requests\Traits;

trait WithMetaTrait {
	
	function withMeta(array $meta, array $subtree = array()) {
		$this->params['meta'] = $meta;
		return $this;
	}
}