<?php
namespace Accepter;

use Exception;
use Nette\Utils\ArrayHash;

class DataObject extends ArrayHash {

    function __get($name) {
        if (!isset($this->name)) return null;
    }
}
