<?php
namespace Accepter;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Support\Events\EventFiringWebDriver;
use Facebook\WebDriver\WebDriverDispatcher;
use Facebook\WebDriver\WebDriverEventListener;
use Facebook\WebDriver\WebDriverHasInputDevices;


use Exception;
use Nette\SmartObject;

class Driver extends EventFiringWebDriver implements WebDriver, WebDriverHasInputDevices, WebDriverEventListener {
    use SmartObject {
        SmartObject::__call as __smartCall;
    }
    use ListenerTrait;

    static $defaultHost = 'http://localhost:4444/wd/hub';
    static $defaultCaps = [
        WebDriverCapabilityType::BROWSER_NAME => 'chrome'
    ];

    protected
        $oRemote,
        $oListener;

    function __construct($oListener) {
        $this->oListener = $oListener;
        $this->oRemote = RemoteWebDriver::create(static::$defaultHost, static::$defaultCaps);
        $oDispatcher = new WebDriverDispatcher;
        $oDispatcher->register($this);
        parent::__construct($this->oRemote, $oDispatcher);
    }

    function getKeyboard() {
        return $this->oRemote->getKeyboard();
    }

    function getMouse() {
        return $this->oRemote->getMouse();
    }

    protected function _fire($event, $data) {
        $handler = 'on'.ucfirst($event);
        if (isset($this->oListener->$handler)) {
            $this->oListener->$handler($data);
        }
        else {
            $this->oListener->onEventDefault($event, $data);
        }
    }
}
