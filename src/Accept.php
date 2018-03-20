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

class Accept {
    static $defaultHost = 'http://localhost:4444/wd/hub';
    static $defaultCaps = [
        WebDriverCapabilityType::BROWSER_NAME => 'chrome'
    ];
    static $keepBrowser = false;

    static function runDriver() {
        $cmd = 'java -jar selenium-server-standalone.jar';
    }

    use SeeTrait;

    protected $oWd;


    function __construct(IWebDriver $oDriver = null) {
        $oDriver = $oDriver ?: RemoteWebDriver::create(static::$defaultHost, static::$defaultCaps);
        $this->oWd = $oDriver;
    }

    function __destruct() {
        if (!static::$keepBrowser) {
            $this->oWd->quit();
        }
    }

    function fail($message) {
        $target = $this->getCallingLine();
        $this->runScript("alert('{$target['file']}:{$target['line']} ({$target['code']})');");
        static::$keepBrowser = true;
        throw new \Exception($message);
    }

    function open($url) {
        $this->oWd->get($url);
        $js = file_get_contents(__DIR__.'/Assets/record.js');
        $this->runScript($js);
    }

    function moveTo($element) {
        $el = $this->findElement($element);
        $this->oWd->getWebDriver()->getMouse()->mouseMove($el->getCoordinates());
    }

    function click($element) {
        $el = $this->findElement($element);
        $el->click();
    }

    function runScript($script) {
        $this->oWd->executeScript($script);
    }

    function record() {
        $this->runScript('window.Recorder.start();');
        $state = WebDriverBy::id('recordState');
        $cond = WebDriverExpectedCondition::elementTextIs($state, 'stop');
        $wait = new WebDriverWait($this->oWd, 60);
        $wait->until($cond);

        $rc = $this->findElement('recordData')->getText();
        $code = $this->generateCode($rc);
        $trg = $this->getCallingLine();
        $this->putLine($trg['file'], $trg['line'], $code);
    }

    protected function generateCode($json) {
        $data = json_decode($json);
        bdump($data);
        if (!$data) return '// nothing recorded';
        return join("\n", [
            "// recorded",
            "\$I->see('{$data->target->id}')",
            "   ->click()",
            "   ->hasText('{$data->target->text}')",
            ";"
        ]);
    }

    protected function getCallingLine() {
        $aTrace = debug_backtrace();
        bdump($aTrace);
        $file = $aTrace[1]['file'];
        $line = $aTrace[1]['line'];

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

    function putLine($file, $line, $code) {
        $aLine = file($file);
        file_put_contents($file.'.bak', implode($aLine));
        array_splice($aLine, $line-1, 0, [$code."\n", "\n"]);
        file_put_contents($file, implode($aLine));
    }

}
