<?php
require_once 'bootstrap.php';

use Accepter\Accept as I;

$file2 = TEMP.'/navtest2.html';
$html = <<<HTML
<body>
    <span>found</span>
<body>
HTML;
file_put_contents($file2, $html);

$file = TEMP.'/navtest.html';
$html = <<<HTML
<body>
    <span>wait</span>
    <a href="$file2">click me</a>
<body>
HTML;
file_put_contents($file, $html);

I::addDefaultListener('simulate', function($I) {
    $I->wait('#recordState')
        ->hasClass('record');
    $I->click('#recordBar .mouse');
    $I->click('click me');
});
I::addDefaultListener('simulate', function($I) {
    $I->wait('#recordState')
        ->hasClass('record');
    $I->click('#recordBar .see');
    $I->see('<span>')
        ->hasText('found')
        ->click();

    $I->click('#recordState');
});

$test = TEMP.'/record.php';
$php = <<<PHP
<?php
use Accepter\Accept as I;
I::record();
PHP;
file_put_contents($test, $php);

I::open($file);
I::see('<a>')
    ->hasText('click me');
include $test;
