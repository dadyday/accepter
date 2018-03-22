<?php
namespace Accepter;

use Facebook\WebDriver\ {
    WebDriver as IWebDriver,
    WebDriverElement as IWebElement,
    WebDriverBy
};

trait SeeTrait {

    protected function findElement($element) {
        if ($element instanceof IWebElement) return $element;

        $aAccess = [];
        if (preg_match('~[/@]\w+~', $element, $aMatch)) {
            $aAccess[] = WebDriverBy::xpath($aMatch[0]);
        }
        if (preg_match('~[#\.]\w+~', $element, $aMatch)) {
            $aAccess[] = WebDriverBy::cssSelector($aMatch[0]);
        }
        if (preg_match('~^<([\w-]+)>$~', $element, $aMatch)) {
            $aAccess[] = WebDriverBy::tagName($aMatch[1]);
        }
        $aAccess[] = WebDriverBy::id($element);
        $aAccess[] = WebDriverBy::className($element);
        $aAccess[] = WebDriverBy::name($element);
        $aAccess[] = WebDriverBy::tagName($element);
        $aAccess[] = WebDriverBy::linkText($element);
        $aAccess[] = WebDriverBy::partialLinkText($element);
        /*
        Css selector - WebDriverBy::cssSelector('h1.foo > small')
        Xpath - WebDriverBy::xpath('(//hr)[1]/following-sibling::div[2]')
        Id - WebDriverBy::id('heading')
        Class name - WebDriverBy::className('warning')
        Name attribute (on inputs) - WebDriverBy::name('email')
        Tag name - WebDriverBy::tagName('h1')
        Link text - WebDriverBy::linkText('Sign in here')
        Partial link text - WebDriverBy::partialLinkText('Sign in')
        */

        foreach ($aAccess as $oAccess) {
            $el = $this->oWd->findElement($oAccess);
            if ($el) return $el;
        }
        return null;
    }

    function _find($what) {
        $el = $this->findElement($what);
        if (!$el) $this->fail("element $what not found");
        return new Element($this, $el);
    }

    function _see($what) {
        $el = $this->_find($what);
        $el->isVisible();
        return $el;
    }
}
