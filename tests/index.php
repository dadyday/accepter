<?php
require_once __DIR__.'/../vendor/autoload.php';
set_time_limit(300);

Tracy\Debugger::enable();
Tracy\Debugger::$maxDepth = 8;
Tracy\Debugger::$maxLength = 500;

#include 'NavTest.php';

use Accepter\Accept as I;
#I::getInstance()->keepBrowser = true;

I::open(__DIR__.'/../demo/deepthought.html');

I::record(false);
