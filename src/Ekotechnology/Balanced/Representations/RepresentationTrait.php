<?php namespace Ekotechnology\Balanced\Representations;

use Ekotechnology\Balanced\Exceptions\UnrecognizedMethod;
use Ekotechnology\Balanced\Requests\Account as AccountActions;
use Ekotechnology\Balanced\Requests\Bank as BankActions;
use Ekotechnology\Balanced\Requests\Card as CardActions;

trait RepresentationTrait {
	private $actions;
	var $instance;
	var $content = array();

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

	function fill($content) {
		if (array($content)) {
			$this->content = $content;
		}
		return $this;
	}

	function setUri($uri) {
		$this->content['uri'] = $uri;
		return $this;
	}

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
				return call_user_method_array($method, $this->actions, $args);
			}
			else {
				throw new UnrecognizedMethod(__CLASS__ . '::' . $method . ' is unknown.  Please check the docs.');
			}
		}
	}
}