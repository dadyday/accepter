<?php
require_once __DIR__.'/../vendor/autoload.php';

use Tester\Assert as Is;
use Accepter as My;

$I = new My\Accept();
Is::type(My\Accept::class, $I);

$I->open(__DIR__.'/testweb.html');

$I->record();
