<?php
require_once __DIR__.'/../bootstrap.php';

use Accepter\Accept as I;

$file = TEMP.'/elementtest.html';
$html = <<<HTML
<html>
<body>
    <input onchange="this.value = 'bar';">
<body>
</html>
HTML;
file_put_contents($file, $html);

I::open($file);
I::click('<input>');
I::type('foo');

I::see('<input>')
    ->hasValue('foo');

I::hit('enter');

I::see('<input>')
    ->hasValue('bar');
