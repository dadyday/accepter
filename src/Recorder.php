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

class Recorder {
    use SmartObject;

    protected
        $oDriver;

    public
        $onData = [],
        $onSimulate = null;

    function __construct($oDriver) {
        $this->oDriver = $oDriver;
    }

    function wait($callback, $time = 10, $interval = 500) {
        $timeout = microtime(true) + $time;
        while ($timeout > microtime(true)) {
            if ($callback()) return true;
            usleep($interval * 1000);
        }
        return false;
        #throw new Exception('timeout');
    }

    function init() {
        if ($this->isRunning()) return;
        $js = include(__DIR__.'/assets/inject.php');
        $this->run($js);
        $ok = $this->wait(function() {
            return $this->isRunning();
        });
        if (!$ok) throw new Exception('recorder will not run');
    }

    function isRunning() {
        return $this->run('return window.Recorder != null;');
    }

    function isRecording() {
        return $this->run('return window.Recorder.state == "recording";');
    }

    function getState() {
        return $this->run('return window.Recorder ? window.Recorder.state : "offline";');
    }

    function start() {
        $this->run('window.Recorder.start();');
        $ok = $this->wait(function() {
            return $this->isRunning() && $this->isRecording();
        });
        if (!$ok) throw new Exception('recorder will not start');
    }

    function restart() {
        $this->run('window.Recorder.restart();');
    }

    function waitLoaded() {
        $ok = $this->wait(function() {
            return $this->run('return document.readyState;') == 'complete';
        });
        if (!$ok) throw new Exception('document will not be ready');
    }

    function handleData() {
        $data = $this->run('return window.Recorder ? window.Recorder.transmitData() : [];');
        #bdump($data, 'handleData');
        $this->onData($data);
    }

    function waitForStop() {
        $end = false;
        do {
            if ($this->onSimulate) $this->onSimulate($this);

            $ok = $this->wait(function() {
                return !$this->isRunning() || !$this->isRecording();
            }, 60);

            $state = $this->getState();
            switch ($state) {
                case 'recording':
                    $this->restart();
                    break;
                case 'playback':
                    $this->handleData();
                    $end = true;
                    break;
                case 'data':
                    $this->handleData();
                    $this->restart();
                    break;
                case 'offline':
                case 'reload':
                    $this->handleData();
                    #$this->continue();
                    $this->waitLoaded();
                    $this->init();
                    $this->start();
                    break;
            }
        }
        while (!$end);
    }

    protected function run($script) {
        return $this->oDriver->executeScript($script);
    }

    protected function findElement($id) {
        return $this->oDriver->findElement(WebDriverBy::id($id));
    }

}
