<?php
namespace Accepter;

use Facebook\WebDriver\ {
    WebDriver as IWebDriver,
    WebDriverElement as IWebElement,
    WebDriverBy,
    Exception
};

trait SeeTrait {

    protected function getFindMechanism($string) {
        $aRet = [];
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
        if (preg_match('~^<([\w-]+)>$~', $string, $aMatch)) $aRet['tagName'] = $aMatch[1];
        elseif (preg_match('~^#([\w-]+)$~', $string, $aMatch)) $aRet['id'] = $aMatch[1];
        elseif (preg_match('~^\.([\w-]+)$~', $string, $aMatch)) $aRet['className'] = $aMatch[1];
        elseif (preg_match('~^/.+~', $string, $aMatch)) $aRet['xpath'] = $string;
        elseif (preg_match('~^[\w-]+$~', $string, $aMatch)) {
            $aRet['id'] = $aRet['name'] = $aRet['tagName'] = $aRet['className'] =
                $aRet['linkText'] = $aRet['partialLinkText'] =
                $string;
        }
        elseif (preg_match('~^([#\.<>+:\[\] \w-])+$~', $string, $aMatch)) {
            $aRet['cssSelector'] = $aRet['linkText'] = $aRet['partialLinkText'] = $string;
        }
        else {
            $aRet['linkText'] = $aRet['partialLinkText'] = $string;
        }
        return $aRet;
    }

    protected function findElement($element) {
        if ($element instanceof IWebElement) return $element;

        $aMech = $this->getFindMechanism($element);
        foreach ($aMech as $mechanism => $search) {
            try {
                $oMech = WebDriverBy::$mechanism($search);
                $el = $this->oWd->findElement($oMech);
                if ($el) return $el;
            }
            catch (Exception\NoSuchElementException $e) {}
            catch (Exception\InvalidSelectorException $e) {}
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
