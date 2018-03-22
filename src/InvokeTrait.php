<?php
namespace Accepter;

trait InvokeTrait {
    function _invoke($func, $args, &$result) {
        if (!$this->_hasMethod($func)) return false;
        $result = call_user_func_array([$this, $func], $args);
        return true;
    }

    function _hasMethod($func) {
        return method_exists($this, $func);
    }
}
