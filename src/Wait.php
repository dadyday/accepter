<?php
namespace Accepter;


use Facebook\WebDriver\WebDriver as IWebDriver;
use Facebook\WebDriver\WebDriverElement as IWebElement;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\Exception\TimeOutException;
use Exception;
use Tester\AssertException;

class Wait {

    protected $oAccept;
    protected $tests;
    protected $lastFail = null;

    function __construct(Accept $oAccept) {
        $this->oAccept = $oAccept;
    }

    function run($tests, $timeout = null) {
        #Assert::$onFailure = function() {};
        try {
            $lastFail = null;
            $wait = new WebDriverWait($this->oAccept->getDriver(), $timeout);
            $wait->until(function () use ($tests, &$lastFail) {
                try {
                    $tests($this->oAccept);
                    return true;
                }
                catch (AssertException $fail) {
                    $lastFail = $fail;
                }
            });
        }
        catch (TimeOutException $e) {
            $this->oAccept->fail("timed out on: ".$lastFail->getMessage());
        }
        return $this;
    }

}
