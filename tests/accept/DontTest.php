<?php
require_once __DIR__.'/../bootstrap.php';

use Accepter\Accept as I;
use Tester\Assert as Is;

$html = <<<HTML
<body>
    <h1>Title</h1>
    <h2>Subtitle</h2>
    <div id="div">Div<b class="class">Span</b></div>
<body>
HTML;
file_put_contents(TEMP.'/donttest.html', $html);

I::open(TEMP.'/donttest.html');

I::dontSee('<h3>');
Is::error(function () {
    I::dontSee('<h2>');
}, Exception::class);

I::dontSee('<div>')
    ->hasId('none')
    ->hasText('Span');

Is::error(function () {
    I::dontSee('<div>')
        ->hasId('div')
        ->hasText('Span');
}, Exception::class);
