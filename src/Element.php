<?php
namespace Accepter;

use Facebook\WebDriver\ {
    WebDriver as IWebDriver,
    WebDriverElement as IWebElement
};

class Element {

    protected $oAccept;
    protected $oElement;

    function __construct(Accept $oAccept, IWebElement $oEl) {
        $this->oAccept = $oAccept;
        $this->oElement = $oEl;
    }

    function fail($message) {
        $this->oAccept->fail($message);
    }

    function __call($name, $args) {
        $func = "_$name";
        if (!method_exists($this, $func)) throw new Exception("method _$name not found");
        try {
            $ret = call_user_func_array([$this, $func], $args);
            return $this;
        }
        catch (Fail $e) {
            $this->fail($e->getMessage());
        }
    }

    protected function _click() {
         $this->oElement->click();
    }

    protected function _hasText($value) {
        $text = $this->oElement->getText();
        if (empty($value)) {
            if (trim($text)) throw new Fail("has not empty text", $text, $value);
        }
        else if (preg_match('~^([\~\\\\]).+\g1[imsex]*$~', $value)) {
            if (!preg_match($value, $text)) throw new Fail("matches not pattern", $text, $value);
        }
        else {
            if (strpos($value, $text) === false) throw new Fail("conatins not text", $text, $value);
        }
    }

}
