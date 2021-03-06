<?php
namespace Accepter;

use Facebook\WebDriver\WebDriver as IWebDriver;
use Facebook\WebDriver\WebDriverElement as IWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception;
use Tester\Assert;

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
        $lString = strtolower($string);

        if (preg_match('~^<([\w-]+)>$~', $string, $aMatch)) $aRet['tagName'] = $aMatch[1];
        elseif (preg_match('~^#([\w-]+)$~', $string, $aMatch)) $aRet['id'] = $aMatch[1];
        elseif (preg_match('~^\.([\w-]+)$~', $string, $aMatch)) $aRet['className'] = $aMatch[1];
        elseif (preg_match('~^/.+~', $string, $aMatch)) $aRet['xpath'] = $string;
        else {
            if (preg_match('~^[\w-]+$~', $string, $aMatch)) {
                $aRet['id'] = $aRet['name'] = $aRet['className'] = $string;
                $aRet['tagName'] = $lString;
            }
            elseif (preg_match('~^([#\.<>+:\[\] \w-])+$~', $string, $aMatch)) {
                $aRet['cssSelector'] = $string;
            }
            # obsolete: $aRet['linkText'] = $aRet['partialLinkText'] = $lString;
            # xpath2 wont work: $aRet['xpath'] = "//*[contains(lower-case(@value), '$lString') or contains(lower-case(text()), '$lString')]";
            $aRet['xpath'] = "//*[contains(translate(@value, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '$lString') or contains(translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '$lString')]";
            # caseinsens: $aRet['xpath'] = "//*[contains(@value, '$string') or contains(text(), '$string')]";
            # notcontain: $aRet['xpath'] = "//*[@value='$string' or text()='$string']";
        }
        return $aRet;
    }

    protected function findElement($desc) {
        $aEl = $this->findElements($desc);
        if (count($aEl) > 1) $this->fail("element $desc not unique enough");
        return $aEl[0];
    }

    protected function findElements($desc) {
        if ($desc instanceof IWebElement) return $desc;

        $aMech = $this->getFindMechanism($desc);
        foreach ($aMech as $mechanism => $search) {
            try {
                $oMech = WebDriverBy::$mechanism($search);
                $el = $this->oWd->findElements($oMech);
                #bdump($el);
                if ($el) return $el;
            }
            catch (Exception\NoSuchElementException $e) {}
            catch (Exception\InvalidSelectorException $e) {
                #dump([$mechanism, $search]);
            }
        }
        #dump($aMech);
        return [];
    }

    function _find($desc) {
        $aEl = $this->findElements($desc);
        Assert::$counter++;
        if (!$aEl) $this->fail("element $desc not found");
        if (count($aEl) > 1) return new ElementList($this, $aEl);
        return new Element($this, $aEl[0]);
    }

    function _see($desc) {
        $el = $this->_find($desc);
        $el->isVisible();
        return $el;
    }

    function _dontSee($desc) {
        $aEl = $this->findElements($desc);
        Assert::$counter++;
        return new ElementList($this, $aEl, false);
    }

    function _focus($desc) {
        $el = $this->_find($desc);
        $el->isVisible();
        $el->focus();
        return $el;
    }

    function _wait($desc, $timeout = 10) {
        $aEl = [];
        $oWait = new Wait($this);
        $oWait->run(function() use (&$aEl, $desc) {
            $aEl = $this->findElements($desc);
            if (!$aEl) $this->fail("element $desc not found");
        }, $timeout);
        // TODO: reuse elapsed time for further timeout
        return new ElementWait($this, $aEl, $timeout);
    }

}
