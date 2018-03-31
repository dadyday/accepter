<?php
require_once 'bootstrap.php';

use Tester\Assert as Is;
use Accepter\Accept as I;

$html = <<<HTML
<html>
<head>
    <style>
        body { color: black; }
        #binda:hover { color: red; }
    </style>
</head>
<body>
    <span id="binda" onclick="this.innerText = 'war da';">bin da</span>
    (click "bin da" and "stop" for this test)
</body>
</html>
HTML;
file_put_contents(TEMP.'/record.html', $html);

$php = <<<'PHP'
<?php
use Accepter\Accept as I;
I::open(TEMP.'/record.html');
I::record();
$I = new I();
$I->record();
PHP;
Is::match('~I::open.*I::record.*'.
           'I = new.*I->record~s', $php);

file_put_contents(TEMP.'/record.php', $php);


I::addDefaultListener('record', function($I) {
    $I->waitUntil(function($I) { $I->see('#recordState')->hasClass('record'); });
    $I->click('#recordBar .mouse');
    $I->click('#binda');
    $I->click('#recordState');
    $I->waitUntil(function($I) { $I->see('#recordState')->hasNotClass('record'); });
});
include(TEMP.'/record.php');


$changed = file_get_contents(TEMP.'/record.php');
Is::contains('I::see(', $changed);
Is::contains('$I->see(', $changed);
Is::match('~I::open.*I::see.*click.*I::record.*'.
           'I = new.*I->see.*click.*I->record~s', $changed);
