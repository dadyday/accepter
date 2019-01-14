<?php
namespace Accepter\Redesign;

use Exception;

class Check {

	protected
		$oAccept,
		$aCheck = [];

	function __construct(Accept $oAccept, $selector) {
		$this->oAccept = $oAccept;
		if ($selector) $this->matches($selector);
	}

	function __destruct() {
		if (!$this->run()) {
			throw new Exception("no element found");
		};
	}

	function __call($name, $aArg) {
		$this->aCheck[] = [$name, $aArg];
		return $this;
	}

	function run() {
		$oEls = clone $this->oAccept->getElementList();
		foreach ($this->aCheck as list($func, $aArg)) {
			$ok = $oEls->$func(...$aArg);
			if (!$ok) break;
		}
		return $ok;
	}
}

class CheckNot extends Check {

	function __destruct() {
		if ($this->run()) {
			throw new Exception("element was found");
		};
	}
}

class Wait extends Check {

	function __construct(Accept $oAccept, $selector, $timeout) {
		parent::__construct($oAccept, $selector);
		$this->timeout = $timeout;
	}

	function run() {
		$end = microtime(true) + $this->timeout;
		do {
			$ok = parent::run();
			if ($ok) break;
		}
		while (microtime(true) < $end);
		return $ok;
	}
}

class Accept {

	static $oInst;
	var $oEls;

	static function create() {
		if (!static::$oInst) {
			static::$oInst = new static();
		}
		return static::$oInst;
	}

	static function __callStatic($name, $aArg) {
		$oInst = static::create();
		return $oInst->$name(...$aArg);
	}

	private function __construct() {
	}

	function getElementList() {
		return $this->oEls;
	}

	protected function find($selector = null) {
		$oCheck = new Check($this, $selector);
		return $oCheck;
	}

	protected function findNot($selector = null) {
		$oCheck = new CheckNot($this, $selector);
		return $oCheck;
	}

	protected function see($selector = null) {
		$oCheck = $this->find($selector);
		$oCheck->isVisible();
		return $oCheck;
	}

	protected function seeNot($selector = null) {
		$oCheck = $this->findNot($selector);
		$oCheck->isVisible();
		return $oCheck;
	}

	protected function wait($selector = null, $timeout = null) {
		if (is_null($timeout) && is_numeric($selector)) {
			$timeout = $selector;
			$selector = null;
		}
		return new Wait($this, $selector, $timeout);
	}

}
