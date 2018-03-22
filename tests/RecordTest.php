<?php
require_once __DIR__.'/../vendor/autoload.php';

use Accepter\Accept as I;

$I = new I();

$I->open(__DIR__.'/testweb.html');

$I->record();

I::record();
