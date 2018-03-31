<?php
require_once 'bootstrap.php';

use Accepter\Accept as I;

$html = <<<HTML
<html>
<head>
    <style>
        h1 { font-weight: bold; }
        h2 { font-weight: normal; }
    </style>
</head>
<body>
    <h1>Title</h1>
    <h2>Subtitle</h2>
    <div id="div">Div<b class="class">Span</b></div>
<body>
</html>
HTML;
file_put_contents(TEMP.'/elementtest.html', $html);

I::open(TEMP.'/elementtest.html');
I::see('<h1>')
    ->hasText('Title')
    ->isVisible()
    ->isBold()
;
#    ->findSibling('<h2>')
I::find('<h2>')
    ->hasNotText('Title')
    ->hasText('/title/i')
    ->isVisible()
    ->isNotBold()
;

I::see('#div .class')
    ->hasText('~^Span$~')
    ->isVisible()
    ->isBold()
;
