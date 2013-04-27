<?php namespace Ekotechnology\Balanced\Representations;

use Ekotechnology\Balanced\Exceptions\UnrecognizedMethod;
use Ekotechnology\Balanced\Requests\Account as AccountActions;
use Ekotechnology\Balanced\Requests\Bank as BankActions;
use Ekotechnology\Balanced\Requests\Card as CardActions;

trait RepresentationTrait {
	private $actions;
	var $instance;
	var $content = array();

	/**
	 * Fire up the representation, give it some context
	 * and possibly some content to prefill it with
	 * @param string $instance
	 * @param array  $content
	 */
	function __construct($instance="", $content=array()) {
		if ($instance) {
			$this->instance = $instance;
		}

		if (!empty($content)) {
			$this->fill($content);
		}

		$class = explode("\\", get_class());
		$className = "Ekotechnology\Balanced\Requests\\";
		$className .= end($class);
		if (class_exists($className)) {
			$this->actions = new $className($this);
		}
	}

	/**
	 * Merges the current representations content with
	 * new content
	 * @param  array  $content  
	 * @return this
	 */
	function fill($content) {
		if (array($content)) {
			$this->content = array_merge($this->content, $content);
		}
		return $this;
	}

	/**
	 * Sets the content for the current representation
	 * @param  array $content
	 * @return this
	 */
	function replace($content) {
		if (is_array($content)) {
			$this->content = $content;
		}
		return $this;
	}

	/**
	 * Sets the URI for the representation
	 * @param string $uri
	 */
	function setUri($uri) {
		$this->content['uri'] = $uri;
		return $this;
	}

	/**
	 * Gets the content for the representation
	 * @return array
	 */
	function getItems() {
		return $this->content;
	}

	function __get($item) {
		if (array_key_exists($item, $this->content)) {
			return $this->content[$item];
		}
		else {
			return null;
		}
	}

	function __set($item, $value) {
		$this->content[$item] = $value;
	}

	function __call($method, $args) {
		if (!method_exists($this, $method)) {
			if (method_exists($this->actions, $method)) {
				return call_user_func_array(array($this->actions, $method), $args);
			}
			else {
				throw new UnrecognizedMethod(__CLASS__ . '::' . $method . ' is unknown.  Please check the docs.');
			}
		}
	}
}