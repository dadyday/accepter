<?php
namespace Accepter;

use Tester\AssertException;

class ElementWait {

    protected
        $oAccept,
        $oElements = null,
        $timeout,
        $interval;

    function __construct(Accept $oAccept, array $aEl, $timeout, $interval = 500) {
        $this->oAccept = $oAccept;
        $this->oElements = new ElementList($oAccept, $aEl);
        $this->timeout = microtime(true) + $timeout;
        $this->interval = $interval * 1000;
    }

    function __call($name, $args) {
        $lastFail = null;
        do {
            try {
                // TODO: reuse ElementList inst
                $oEls = clone $this->oElements;
                $oEls->__call($name, $args);
                $this->oElements = $oEls;
                return $this;
            }
            catch (AssertException $e) {
                $lastFail = $e;
                usleep($this->interval);
            }
        }
        while (microtime(true) < $this->timeout);

        $this->oAccept->fail("timed out on: ".$lastFail->getMessage());
    }
}
