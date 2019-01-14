<?php
namespace Accepter\Redesign;

require_once __DIR__.'/cfg.php';

use Tester\Assert as Is;
use Accepter\Redesign\Accept as I;


class MyElList extends ElementList {

	function _test() {
		global $oAccept;
		$this->add(new MyEl('span', 'hello baz'));
		return true;
	}

}
class MyEl extends Element {

	function isVisible() {
		return true;
	}

}

$oAccept = Accept::create();
$oAccept->oEls = new MyElList();
$oAccept->oEls->add(new MyEl('li', 'hello there'));
$oAccept->oEls->add(new MyEl('li', 'hello world'));
$oAccept->oEls->add(new MyEl('span', 'hello universe'));
$oAccept->oEls->add(new MyEl('span', 'foo bar'));

I::find()
	->hasTag('li')
	->hasText('hello world')
;

I::findNot('li')
	->withText('hello universe')
;

I::see('span')
	->withoutText('hello universe')
;

I::wait(1)
	->text('hello')
	->_test()
	->count(4)
;
