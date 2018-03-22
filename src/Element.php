<?php
namespace Accepter;

use Facebook\WebDriver\ {
    WebDriver as IWebDriver,
    WebDriverElement as IWebElement
};
use Exception;

class Element {

    use InvokeTrait;

    protected $oAccept;
    protected $oElement;

    function __construct(Accept $oAccept, IWebElement $oEl) {
        $this->oAccept = $oAccept;
        $this->oElement = $oEl;
    }

    function fail($message, $actual, $expected) {
        $this->oAccept->fail($message, $actual, $expected);
    }

    function __call($name, $args) {
        if (!$this->_invoke("_$name", $args, $result)) {
            if (!$this->_invokeNot($name, $args, $result)) {
                throw new Exception("method $name not found");
            }
        };
        $result += [1 => 'not described', 2 => null, 3 => null];
        $result[1] = preg_replace('~(\*(.*)\*)~', '$2', $result[1]);
        if (!$result[0]) $this->fail($result[1], $result[2], $result[3]);
        return $this;
    }

    protected function _invokeNot($name, $args, &$result) {
        if (!preg_match('~^(is|has)No(?:t?)([A-Z]\w*)~', $name, $aMatch)) return false;
        $func = "_$aMatch[1]$aMatch[2]";
        if (!$this->_invoke($func, $args, $result)) return false;

        $result[0] = !$result[0];
        $result[1] = preg_replace('~(\*(.*)\*)~', '', $result[1]);
        return true;
    }

    protected function _click() {
         $this->oElement->click();
         return true;
    }

    protected function _isVisible() {
         return [$this->oElement->isDisplayed(), "is *not* visible"];
    }

    protected function _isBold() {
        $weight = $this->oElement->getCssValue("font-weight");
        return [
            (is_numeric($weight) && $weight >= 500) ||
            ($weight == 'bold'),
            "is *not* bold",
            $weight,
        ];
    }

    protected function _hasText($value) {
        $text = $this->oElement->getText();
        if (empty($value)) {
            return [!trim($text), "%1 has *not* empty text", $text, $value];
        }
        if (preg_match('~^([/\~#]).+(\1)[imsex]*$~', $value)) {
            return [preg_match($value, $text), "%1 matches *not* pattern %2", $text, $value];
        }
        return [strpos($value, $text) !== false, "%1 contains *not* text %2", $text, $value];
    }

}
