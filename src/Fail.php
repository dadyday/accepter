<?php
namespace Accepter;

class Fail extends \Exception {

    protected $should;
    protected $have;

    function __construct($message, $should, $have) {
        $this->should = $should;
        $this->have = $have;
        parent::__construct($message);
    }

    function getMessage() {
        return parent::getMessage() . $this->have . ' instead of ' . $this->should;
    }
}
