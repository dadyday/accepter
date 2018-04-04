<?php
namespace Accepter;

use Facebook\WebDriver\WebDriverEventListener;
use Facebook\WebDriver\Support\Events\EventFiringWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Support\Events\EventFiringWebElement;
use Facebook\WebDriver\Exception\WebDriverException;


trait ListenerTrait {

    abstract function _fire($event, $data);
    
    /**
     * @param string $url
     * @param EventFiringWebDriver $driver
     */
    public function beforeNavigateTo($url, EventFiringWebDriver $driver) {
        $this->_fire('beforeNavigateTo', func_get_args());
    }

    /**
     * @param string $url
     * @param EventFiringWebDriver $driver
     */
    public function afterNavigateTo($url, EventFiringWebDriver $driver) {
        $this->_fire('afterNavigateTo', func_get_args());
    }

    /**
     * @param EventFiringWebDriver $driver
     */
    public function beforeNavigateBack(EventFiringWebDriver $driver) {
        $this->_fire('beforeNavigateBack', func_get_args());
    }

    /**
     * @param EventFiringWebDriver $driver
     */
    public function afterNavigateBack(EventFiringWebDriver $driver) {
        $this->_fire('afterNavigateBack', func_get_args());
    }

    /**
     * @param EventFiringWebDriver $driver
     */
    public function beforeNavigateForward(EventFiringWebDriver $driver) {
        $this->_fire('beforeNavigateForward', func_get_args());
    }

    /**
     * @param EventFiringWebDriver $driver
     */
    public function afterNavigateForward(EventFiringWebDriver $driver) {
        $this->_fire('afterNavigateForward', func_get_args());
    }

    /**
     * @param WebDriverBy $by
     * @param EventFiringWebElement|null $element
     * @param EventFiringWebDriver $driver
     */
    public function beforeFindBy(WebDriverBy $by, $element, EventFiringWebDriver $driver) {
        $this->_fire('beforeFindBy', func_get_args());
    }

    /**
     * @param WebDriverBy $by
     * @param EventFiringWebElement|null $element
     * @param EventFiringWebDriver $driver
     */
    public function afterFindBy(WebDriverBy $by, $element, EventFiringWebDriver $driver) {
        $this->_fire('afterFindBy', func_get_args());
    }

    /**
     * @param string $script
     * @param EventFiringWebDriver $driver
     */
    public function beforeScript($script, EventFiringWebDriver $driver) {
        $this->_fire('beforeScript', func_get_args());
    }

    /**
     * @param string $script
     * @param EventFiringWebDriver $driver
     */
    public function afterScript($script, EventFiringWebDriver $driver) {
        $this->_fire('afterScript', func_get_args());
    }

    /**
     * @param EventFiringWebElement $element
     */
    public function beforeClickOn(EventFiringWebElement $element) {
        $this->_fire('beforeClickOn', func_get_args());
    }

    /**
     * @param EventFiringWebElement $element
     */
    public function afterClickOn(EventFiringWebElement $element) {
        $this->_fire('afterClickOn', func_get_args());
    }

    /**
     * @param EventFiringWebElement $element
     */
    public function beforeChangeValueOf(EventFiringWebElement $element) {
        $this->_fire('beforeChangeValueOf', func_get_args());
    }

    /**
     * @param EventFiringWebElement $element
     */
    public function afterChangeValueOf(EventFiringWebElement $element) {
        $this->_fire('afterChangeValueOf', func_get_args());
    }

    /**
     * @param WebDriverException $exception
     * @param EventFiringWebDriver $driver
     */
    public function onException(WebDriverException $exception, EventFiringWebDriver $driver = null) {
        $this->_fire('onException', func_get_args());
    }

}
