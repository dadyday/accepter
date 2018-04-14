<?php
namespace Accepter;

use Facebook\WebDriver\WebDriver as IWebDriver;
use Facebook\WebDriver\WebDriverElement as IWebElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Support\Events\EventFiringWebDriver;
use Facebook\WebDriver\WebDriverDispatcher;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverEventListener;


use Exception;
use Tester\Assert;
use Nette\SmartObject;
use Nette\Utils\Json;

class Accept {
    use SmartObject {
        SmartObject::__call as __smartCall;
    }

    static $defaultListener = [];

    static function addDefaultListener($event, $callable) {
        static::$defaultListener[$event][] = $callable;
        if (static::$oInst) static::$oInst->addListener($event, $callable);
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
        $onEventDefault = [],
        $onSimulate = [],
        $onAfterNavigateTo = [];

    function __construct() {
        #$capabilities  = DesiredCapabilities::chrome();
        #$chromeOptions = (new ChromeOptions)->addArguments(['headless', 'disable-gpu']);
        #$capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        $this->onAfterNavigateTo[] = function($url) {
            #$js = include(__DIR__.'/assets/inject.php');
            #$this->_runScript($js);
        };

        $this->addEventListener(static::$defaultListener);

        if (static::$oInst) {
            static::$oInst->keepBrowser = true;
            $this->oWd = static::$oInst->getDriver();
        }
        else {
            $this->oWd = new Driver($this);
        }
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

    function addListener($event, $listener) {
        $event = 'on'.ucfirst($event);
        if (!isset($this->$event)) throw new Exception("event $event not defined");
        array_push($this->$event, $listener);
    }

    function addEventListener($aEventListener) {
        foreach($aEventListener as $event => $aListener) {
            $event = 'on'.ucfirst($event);
            if (!isset($this->$event)) throw new Exception("event $event not defined");
            foreach ($aListener as $listener) {
                array_push($this->$event, $listener);
            };
        }
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
        #$js = include(__DIR__.'/assets/inject.php');
        #$this->_runScript($js);
    }

    function _moveTo($element) {
        $el = $this->findElement($element);
        $this->oWd->getMouse()->mouseMove($el->getCoordinates());
    }

    function _click($desc) {
        $el = $this->findElement($desc);
        if (!$el) $this->fail("element $desc not found");
        $el->click();
    }

    function _type($keys) {
        $this->oWd->getKeyboard()->sendKeys($keys);
    }

    function _hit($key) {
        $key = strtoupper($key);
        $oRefl = new \ReflectionClass(WebDriverKeys::class);
        $aConst = $oRefl->getConstants();
        if (!isset($aConst[$key])) throw new Exception("unknown key $key hitted");
        $this->oWd->getKeyboard()->sendKeys($aConst[$key]);
    }

    function _waitUntil($callable, $timeout = 10) {
        $oWait = new Wait($this);
        return $oWait->run($callable, $timeout);
    }

    function _runScript($script) {
        $this->oWd->executeScript($script);
    }

    function _record($writeBack = true) {
        $oWriter = new CodeWriter();
        $oWriter->findTarget(self::class);

        $oGen = new CodeGenerator([
            'tab' => $oWriter->tab,
            'lf' => $oWriter->lf,
            'prefix' => $oWriter->getPrefix('record(', $indent),
            'indent' => $indent,
            'commentOut' => !$writeBack,
        ]);

        $oRecorder = new Recorder($this->oWd);
        $oRecorder->init();
        $oRecorder->onData[] = function($data) use ($oWriter, $oGen) {

            $oGen->runAll($data);
            $aCode = $oGen->getCodeArray();

            $oWriter->addCode($aCode);
            $oWriter->save();

            $oGen->reset();
        };

        $oRecorder->start();
        $oRecorder->onSimulate[] = function () {
            static $n = 0;
            $func = isset($this->onSimulate[$n]) ? $this->onSimulate[$n] : null;
            if ($func) $func($this);
            $n = $n >= count($this->onSimulate) ? 0 : $n+1;
        };
        $oRecorder->waitForStop();



        /*
        $this->_runScript('window.Recorder.start();');

        $this->onRecord($this);

        $state = WebDriverBy::id('recordResult');
        $cond = WebDriverExpectedCondition::presenceOfElementLocated($state);
        $wait = new WebDriverWait($this->oWd, 60, 500);

        do {
            $wait->until($cond);

            $oWriter = new CodeWriter();
            $oWriter->findTarget(self::class);

            $json = $this->findElement('recordResult')->getText();
            $data = Json::decode($json, Json::FORCE_ARRAY);

            #$code = $this->generateCode($rc, $prefix);
            $oGen = new CodeGenerator([
                'tab' => $oWriter->tab,
                'lf' => $oWriter->lf,
                'prefix' => $oWriter->getPrefix('record(', $indent),
                'indent' => $indent,
            ]);
            $oGen->runAll($data);
            $aCode = $oGen->getCodeArray();

            $oWriter->addCode($aCode);
            $oWriter->save();

            $state = $this->findElement('recordState')->getAttribute('class');
            $end = empty($state);
            if ($state == 'reload') {
                $this->_runScript('return document.readyState');
                $js = include(__DIR__.'/assets/inject.php');
                $this->_runScript($js);
                $this->_runScript('window.Recorder.start();');
            }
        }
        while (!$end);
        */
    }

}
