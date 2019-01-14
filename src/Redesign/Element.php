<?php
namespace Accepter\Redesign;

use Exception;

class Element {

	var
		$tag,
		$id,
		$text,
		$aAttr,
		$value,
		$aClass,
		$aStyle,
		$aColor,
		$aDeco;

	function __construct($tag, $text) {
		$this->tag = $tag;
		$this->text = $text;
	}

	function __call($name, $aArg) {
		if (preg_match('~^(Is|Has|With|)(Not|No|Without|)([A-Z].*)$~', ucfirst($name), $aMatch)) {
			if ($aMatch[2]) {
				return $this->hasNot($aMatch[3], ...$aArg);
			}
			else {
				return $this->has($aMatch[3], ...$aArg);
			}
		}
		throw new Exception("Element method '$name' not defined");
	}

	function has($cond, ...$aArg) {
		$aFunc = ['is'.$cond, 'has'.$cond];
		foreach ($aFunc as $func) {
			if (method_exists($this, $func)) return $this->$func(...$aArg);
		}
		throw new Exception("Condition '$cond' not defined");
	}

	function hasNot($cond, ...$aArg) {
		$aFunc = ['isNo'.$cond, 'isNot'.$cond, 'hasNo'.$cond, 'hasNot'.$cond];
		foreach ($aFunc as $func) {
			if (method_exists($this, $func)) return $this->$func(...$aArg);
		}
		return !$this->has($cond, ...$aArg);
	}

	function isTag($tag) {
		return $this->tag == $tag;
	}

	function hasText($text) {
		return strpos($this->text, $text) !== false;
	}

	function matches($selector) {
		return $this->isTag($selector);
	}
}
