<?php
namespace Accepter;

use Facebook\WebDriver\WebDriver as IWebDriver;
use Facebook\WebDriver\WebDriverElement as IWebElement;
use Exception;

class ElementList {

    protected $oAccept;
    protected $aElement = [];

    function __construct(Accept $oAccept, array $aEl) {
        $this->oAccept = $oAccept;
        foreach ($aEl as $oEl) {
            $this->aElement[] = new Element($oAccept, $oEl);
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
                if (!$this->aElement) throw $e;
            }
        }
        return $this;
    }
}
