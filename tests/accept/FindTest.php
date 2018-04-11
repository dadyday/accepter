<?php
require_once __DIR__.'/../bootstrap.php';

use Accepter\Accept as I;

$file = TEMP.'/findtest.html';
$html = <<<HTML
<body>
    <span class="cls" id="idx">found</span>
    <a>click me</a>
<body>
HTML;
file_put_contents($file, $html);

I::open($file);
I::find('<span>')->hasText('found');
I::find('span')->hasText('found');
I::find('body span')->hasText('found');
I::find('//span')->hasText('found');
I::find('#idx')->hasText('found');
I::find('idx')->hasText('found');
I::find('.cls')->hasText('found');
I::find('cls')->hasText('found');
I::find('click me')->hasText('click me');
I::find('click')->hasText('click me');
