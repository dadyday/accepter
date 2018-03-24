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
    #use SmartObject;

    static $defaultHost = 'http://localhost:4444/wd/hub';
    static $defaultCaps = [
        WebDriverCapabilityType::BROWSER_NAME => 'chrome'
    ];

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
        $keepBrowser = false;


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
        $this->oWd = $oDriver;
        static::$oInst = $this;
    }

    function __destruct() {
        if (!$this->keepBrowser) {
            $this->oWd->quit();
        }
    }

    function __call($name, $args) {
        if (!$this->_invoke("_$name", $args, $result)) throw new Exception("method $name not found");
        return $result ?: $this;
    }

    function getDriver() {
        return $this->oWd;
    }

    function fail($message, $actual, $expected) {
        #$target = $this->caller;
        #$this->_runScript("alert('{$target['file']}:{$target['line']} ({$target['code']})');");
        #$this->keepBrowser = true
        #$message = sprintf("%s, %s instead of %s", $message, $actual, $expected);
        #throw new Exception($message);
        Assert::fail($message, $actual, $expected);
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

    function _runScript($script) {
        $this->oWd->executeScript($script);
    }

    function _record() {
        $this->_runScript('window.Recorder.start();');
        $state = WebDriverBy::id('recordResult');
        $cond = WebDriverExpectedCondition::presenceOfElementLocated($state);
        $wait = new WebDriverWait($this->oWd, 60);
        $wait->until($cond);

        $oWriter = new CodeWriter();
        $oWriter->findTarget(self::class);
        $prefix = $oWriter->getCodePrefix('record(');

        $rc = $this->findElement('recordResult')->getText();
        $code = $this->generateCode($rc, $prefix);

        $oWriter->addCode($code);
        $oWriter->save();
        dump($oWriter);
        #$trg = $this->caller;
        #$this->putLine($trg['file'], $trg['line'], $code);
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
/*
    protected function getCallingLine() {
        $aTrace = debug_backtrace();
        #bdump($aTrace);
        for ($n = 1; $n < count($aTrace); $n++) {
            if (!isset($aTrace[$n]['file'])) continue;
            if ($aTrace[$n]['file'] == __FILE__) continue;
            #if ($aTrace[$n]['class'] == self::class) continue;
            break;
        };
        if ($n >= count($aTrace)) throw new Exception('caller not found');
        bdump($aTrace[$n]);
        $file = $aTrace[$n]['file'];
        $line = $aTrace[$n]['line'];

        $file = preg_replace('~\\\\~', '/', $file);
        $file = preg_replace('~\'~', '\\\'', $file);
        $aLine = file($file, FILE_IGNORE_NEW_LINES);
        $code = $aLine[$line-1];
        return [
            'file' => $file,
            'line' => $line,
            'code' => $code,
        ];
    }

    function getOffset() {
        return $this->aOffset;
    }

    protected function putLine($file, $line, $code) {
        if (empty($file)) throw new Exception('file empty while putting recorded lines');
        $aLine = file($file);
        $bak = dirname($file).'/_'.basename($file).'.bak';
        file_put_contents($bak, implode($aLine));

        $code = array_map(function($item) { return $item."\n"; }, $code);
        if (!isset($this->aOffset[$file])) $this->aOffset[$file] = 0;
        $line += $this->aOffset[$file];
        $this->aOffset[$file] += count($code);

        array_splice($aLine, $line-1, 0, $code);
        file_put_contents($file, implode($aLine));
    }
*/
}
