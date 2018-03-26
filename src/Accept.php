<?php
namespace Accepter;

use Facebook\WebDriver\ {
    WebDriver as IWebDriver,
    WebDriverElement as IWebElement,
    WebDriverExpectedCondition,
    Remote\RemoteWebDriver,
    Remote\WebDriverCapabilityType,
    Support\Events\EventFiringWebDriver,
    WebDriverDispatcher,
    WebDriverBy,
    WebDriverWait
};
use Exception;
use Tester\Assert;
use Nette\SmartObject;

class Accept {
    use SmartObject {
        SmartObject::__call as __smartCall;
    }

    static $defaultHost = 'http://localhost:4444/wd/hub';
    static $defaultCaps = [
        WebDriverCapabilityType::BROWSER_NAME => 'chrome'
    ];
    static $defaultListener = [];

    static function addDefaultListener($event, $callable) {
        static::$defaultListener[$event][] = $callable;
    }

    static function runDriver() {
        $cmd = 'java -jar selenium-server-standalone.jar';
    }

    protected static $oInst;

    static function getInstance() {
        if (!static::$oInst) static::$oInst = new static();
        return static::$oInst;
    }

    static function __callStatic($name, $args) {
        return call_user_func_array([static::getInstance(), $name], $args);
    }

    use SeeTrait;
    use InvokeTrait;

    protected
        $oWd;

    public
        $keepBrowser = false,
        $onRecord;

    function __construct(IWebDriver $oDriver = null) {
        if (!$oDriver) {
            if (static::$oInst) {
                static::$oInst->keepBrowser = true;
                $oDriver = static::$oInst->getDriver();
            }
            else {
                $oDriver = RemoteWebDriver::create(static::$defaultHost, static::$defaultCaps);
            }
        }

        foreach(static::$defaultListener as $event => $aListener) {
            $event = 'on'.ucfirst($event);
            foreach ($aListener as $listener) {
                $this->$event[] = $listener;
            };
        }

        $this->oWd = $oDriver;
        static::$oInst = $this;
    }

    function __destruct() {
        if (!$this->keepBrowser) {
            $this->oWd->quit();
        }
    }

    function __call($name, $args) {
        if (!$this->_invoke("_$name", $args, $result)) {
            //throw new Exception("method $name not found");
            $this->__smartCall($name, $args);
        }
        return $result ?: $this;
    }

    function getDriver() {
        return $this->oWd;
    }

    function fail($message, $actual = null, $expected = null) {
        #$target = $this->caller;
        #$this->_runScript("alert('{$target['file']}:{$target['line']} ({$target['code']})');");
        #$this->keepBrowser = true
        #$message = sprintf("%s, %s instead of %s", $message, $actual, $expected);
        #throw new Exception($message);
        Assert::fail($message, $actual, $expected);
        #throw new Fail()
    }

    function _open($url) {
        $this->oWd->get($url);
        $js = include(__DIR__.'/assets/inject.php');
        $this->_runScript($js);
    }

    function _moveTo($element) {
        $el = $this->findElement($element);
        $this->oWd->getWebDriver()->getMouse()->mouseMove($el->getCoordinates());
    }

    function _click($element) {
        $el = $this->findElement($element);
        $el->click();
    }

    function _waitUntil($callable, $timeout = 10) {
        $oWait = new Wait($this);
        return $oWait->run($callable, $timeout);
    }

    function _runScript($script) {
        $this->oWd->executeScript($script);
    }

    function _record() {
        $this->_runScript('window.Recorder.start();');

        $this->onRecord($this);

        $state = WebDriverBy::id('recordResult');
        $cond = WebDriverExpectedCondition::presenceOfElementLocated($state);
        $wait = new WebDriverWait($this->oWd, 60, 500);
        $wait->until($cond);

        $oWriter = new CodeWriter();
        $oWriter->findTarget(self::class);
        $prefix = $oWriter->getCodePrefix('record(');

        $rc = $this->findElement('recordResult')->getText();
        $code = $this->generateCode($rc, $prefix);

        $oWriter->addCode($code);
        $oWriter->save();
    }

    protected function generateCode($json, $prefix) {
        $data = json_decode($json);
        bdump($data);
        if (!$data) return '// nothing recorded';
        return [
            "// recorded",
            "{$prefix}see('{$data->target->id}')",
            "   ->click()",
            "   ->hasText('{$data->target->text}')",
            ";"
        ];
    }

}
