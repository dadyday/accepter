<?php
namespace Accepter\Redesign;

use Exception;

class ElementList {

	protected
		$aElement = [];

	function add(Element $oEl) {
		$this->aElement[] = $oEl;
	}

	function __call($name, $aArg) {
		return $this->filterBy($name, $aArg);
	}

	function filterBy($func, $aArg) {
		foreach ($this->aElement as $key => $oEl) {
			if (!$oEl->$func(...$aArg)) unset($this->aElement[$key]);
		}
		return !!count($this->aElement);
	}

	function isEmpty() {
		return !count($this->aElement);
	}

	function isNotEmpty() {
		return !!count($this->aElement);
	}

	function count($times) {
		return count($this->aElement) == $times;
	}

}
