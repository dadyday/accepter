<?php
namespace Accepter;

class Fail extends \Exception {

    protected $should;
    protected $have;

    function __construct($message, $should, $have) {
        $this->should = $should;
        $this->have = $have;
        parent::__construct($message . $this->have . ' instead of ' . $this->should);
    }
}
