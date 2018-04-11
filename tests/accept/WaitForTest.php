<?php
require_once __DIR__.'/../bootstrap.php';

use Tester\Assert as Is;
use Accepter\Accept as I;

$html = <<<HTML
<html>
<head>
    <style>
        h1 { font-weight: bold; }
        h2 { font-weight: normal; }
    </style>
    <script>
    function start() {
        setTimeout(function() { document.getElementById('h1').innerText = 'Is there!'; }, 5000);
        setTimeout(function() { document.getElementById('h1').style.fontWeight = 'normal'; }, 7000);
    };
    </script>
</head>
<body>
    <h1 id="h1" onclick="start()">will be set soon ...</h1>
    <h2>Subtitle</h2>
<body>
</html>
HTML;
file_put_contents(TEMP.'/waitfortest.html', $html);

I::open(TEMP.'/waitfortest.html');
I::see('#h1')
    ->hasText('soon')
    ->click()
;
I::waitUntil(function () {
    I::see('#h1')
        ->hasText('there')
        ->isNotBold()
    ;
}, 10);

I::open(TEMP.'/waitfortest.html');

Is::exception(function() {
    I::waitUntil(function () {
        I::see('#h1')
            ->hasText('there')
            ->isNotBold()
        ;
    }, 2);
}, Tester\AssertException::class, '~timed out .* contains not text~');
