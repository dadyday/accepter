<?php
require_once __DIR__.'/../vendor/autoload.php';

Tracy\Debugger::enable();
Tracy\Debugger::$maxDepth = 8;
Tracy\Debugger::$maxLength = 500;

#include 'app/test.php';
include 'FindMultiTest.php';
