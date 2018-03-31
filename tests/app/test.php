<?php
require_once __DIR__.'/../bootstrap.php';
use Accepter\Accept as I;

I::open(__DIR__.'/testweb.html');
I::click('#bin-da');
I::click('#bin-auch-da .mitKlasse');
I::record();
