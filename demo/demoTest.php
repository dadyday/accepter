<?php
require_once __DIR__.'/../vendor/autoload.php';

use Accepter\Accept as I;

I::open(__DIR__.'/deepthought.html');

I::record();
