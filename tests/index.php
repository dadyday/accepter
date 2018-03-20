<?php
require_once __DIR__.'/../vendor/autoload.php';

Tracy\Debugger::enable();
Tracy\Debugger::$maxDepth = 8;

include 'RecordTest.php';
