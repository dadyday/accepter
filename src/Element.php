<?php
namespace Accepter;

use Facebook\WebDriver\WebDriver as IWebDriver;
use Facebook\WebDriver\WebDriverElement as IWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;

use Exception;

class Element {

    use InvokeTrait;

    protected $oAccept;
    protected $oElement;

    function __construct(Accept $oAccept, IWebElement $oEl) {
        $this->oAccept = $oAccept;
        $this->oElement = $oEl;
    }

    function find($value, $type = null) {
        try {
            return $this->oElement->findElement(WebDriverBy::$type($value));
        }
        catch (NoSuchElementException $e) {}
        try {
            return $this->oAccept->getDriver()->findElement(WebDriverBy::$type($value));
        }
        catch (NoSuchElementException $e) {}
    }

    function getSelectedOption() {
        return $this->find('option[@selected]', 'xpath');
    }

    function getLabelField() {
        $tag = $this->oElement->getTagName();
        if ($tag !== 'label') {
            return null;
        };
        $for = $this->oElement->getAttribute('for');
        if ($for) return $this->find($for, 'id');
        return $this->find('input, textarea, select', 'cssSelector');
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
        #bdump([$name, $result], 'element-result');
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
        return [true];
    }

    protected function _focus() {
        $id = $this->oElement->getAttribute("id");
        $this->oAccept->runScript("document.getElementById('$id').focus();");
        #$this->oElement->click();
        return [true];
    }

    protected function _select($from = null, $to = null) {
        $id = $this->oElement->getAttribute("id");
        $text = $this->oElement->getText();
        if (!is_numeric($from)) {
            $search = empty($from) ? $text : $from;
            $from = strpos($text, $search);
            if ($from === false) return [ false, "%1 not found for select in %2", $search, $text];
            $to = $from + strlen($search);
        }
        if (is_null($from)) {
            $from = 0;
        }
        if (is_null($to)) {
            $to = strlen($text);
        }
        if ($from < 0) {
            $from = strlen($text) - $from;
        }
        if ($to < 0) {
            $to = strlen($text) - $to;
        }
        $script = "
            var selection = window.getSelection();
            var range = document.createRange();
            var el = document.getElementById('$id');
            var tx = el.childNodes[0];
            range.setStart(tx, $from);
            range.setEnd(tx, $to);

            var triggerMouseEvent = function (node, eventType) {
                var clickEvent = document.createEvent ('MouseEvents');
                clickEvent.initEvent (eventType, true, true);
                node.dispatchEvent (clickEvent);
            };

            triggerMouseEvent (el, 'mouseover');
            triggerMouseEvent (el, 'mousedown');
            selection.empty();
            selection.addRange(range);
            triggerMouseEvent (el, 'mouseup');
            triggerMouseEvent (el, 'click');
        ";
        $this->oAccept->runScript($script);
        return [true];
    }

    protected function _enter($text) {
         $this->_type($text);
         $this->oAccept->hit('ENTER');
         return [true];
    }

    protected function _type($text) {
         $this->oAccept->type($text);
         return [true];
    }

    protected function _hit($key) {
         $this->oAccept->hit($key);
         return [true];
    }

    protected function _isVisible() {
         return [$this->oElement->isDisplayed(), "is *not* visible"];
    }

    protected function _isBold() {
        $weight = $this->oElement->getCssValue("font-weight");
        return [
            (is_numeric($weight) && $weight >= 500) ||
            ($weight == 'bold'),
            "%1 is *not* bold",
            $weight,
        ];
    }

    protected function _hasId($value) {
        $value = ''.$value;
        $id = $this->oElement->getAttribute("id");
        return [
            ($value) ? $value === $id : empty($id),
            "%1 has *not* id %2",
            $id,
            $value
        ];
    }

    protected function _hasClass($value) {
        $class = $this->oElement->getAttribute("class");
        return [
            (preg_match('~\b'.preg_quote($value).'\b~', $class)),
            "%1 has *not* class %2",
            $class,
            $value
        ];
    }

    protected function _hasValue($value) {
        $text = $this->oElement->getAttribute('value');
        if ($this->oElement->getTagName() == 'select') {
            $text .= ' '. $this->getSelectedOption()->getText();
        }
        // TODO: get value from check, select etc too
        if (empty($value)) {
            return [!trim($text), "%1 has *not* empty value", $text, $value];
        }
        if (preg_match('~^([/\~#]).+(\1)[imsex]*$~', $value)) {
            return [preg_match($value, $text), "value %1 matches *not* pattern %2", $text, $value];
        }
        return [strpos($text, $value) !== false, "value %1 contains *not* %2", $text, $value];
    }

    protected function _isSelected() {
        $is = $this->oElement->getAttribute('selected');
        return [!empty($is), "option is *not* selected"];
    }

    protected function _hasName($should) {
        $is = $this->oElement->getAttribute('name');
        if (empty($should)) {
            return [!trim($is), "%1 has *no* name", $is, $should];
        }
        return [strpos($is, $should) === 0, "%1 is *not* of name %2", $is, $should];
    }

    protected function _hasText($value) {
        $text = $this->oElement->getText();
        if (empty($value)) {
            return [!trim($text), "%1 has *not* empty text", $text, $value];
        }
        if (preg_match('~^([/\~#]).+(\1)[imsex]*$~', $value)) {
            return [preg_match($value, $text), "%1 matches *not* pattern %2", $text, $value];
        }
        return [strpos($text, $value) !== false, "%1 contains *not* text %2", $text, $value];
    }

    protected function _hasTagName($name) {
        $tag = $this->oElement->getTagName();
        return [$tag == $name, "element %1 is *not* of tag %2", $tag, $name];
    }

    protected function _isTag($name) {
        return $this->_hasTagName($name);
    }

    protected function _labelFor() {
        $tag = $this->oElement->getTagName();
        if ($tag !== 'label') {
            return [false, "element %1 is *not* a label", $tag];
        }
        $this->oElement = $this->getLabelField();
        return [!empty($this->oElement), "element %1 has *not* a field", $tag];
    }

}
