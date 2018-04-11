<?php
require_once __DIR__.'/../bootstrap.php';

use Tester\Assert as Is;
use Accepter\Accept as I;

$file = TEMP.'/findtest.html';
$html = <<<HTML
<body>
    <style> .bold { font-weight: bold } </style>
    <span>notme</span>
    <span class="cls bold">notme</span>
    <span class="cls">notme</span>
    <span class="cls" id="idx">notme</span>
    <span class="cls" id="idx">found</span>
<body>
HTML;
file_put_contents($file, $html);

I::open($file);
$el = I::find('<span>')
    ->hasClass('cls')
    ->isNotBold()
    ->hasId('idx')
    ->hasText('found');

#dump($el);

Is::exception(function () use ($el) {
    $el->isNotVisible();
}, Tester\AssertException::class);
