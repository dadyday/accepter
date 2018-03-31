<?php
require_once 'bootstrap.php';

use Accepter\Accept as I;

$file = TEMP.'/findtest.html';
$html = <<<HTML
<body>
    <span class="cls" id="idx">found</span>
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
