<?php
namespace Accepter;

use Facebook\WebDriver\WebDriver as IWebDriver;
use Facebook\WebDriver\WebDriverElement as IWebElement;
use Exception;

class ElementList {

    protected
        $oAccept,
        $aElement = [],
        $check = true;

    function __construct(Accept $oAccept, array $aEl, $check = true) {
        $this->oAccept = $oAccept;
        foreach ($aEl as $oEl) {
            $this->aElement[] = new Element($oAccept, $oEl);
        };
        $this->check = $check;
    }

    function __destruct() {
        if (!$this->check && $this->aElement) {
            $this->oAccept->fail("element was found");
        }
    }

    function __call($name, $args) {
        foreach ($this->aElement as $i => $oElement) {
            try {
                $oElement->__call($name, $args);
            }
            catch (Exception $e) {
                // TODO: make this inst reusable for ElementWait class
                unset($this->aElement[$i]);
                if ($this->check && !$this->aElement) throw $e;
            }
        }
        return $this;
    }
}
