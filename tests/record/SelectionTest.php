<?php
require_once __DIR__.'/../bootstrap.php';

use Tester\Assert as Is;
use Accepter\Accept as I;

$file = TEMP.'/selection.html';
$html = <<<HTML
<html>
<body>
    <span id="sentence">a bit longer example sentence</span>
</body>
</html>
HTML;
file_put_contents($file, $html);

I::addDefaultListener('simulate', function($I) {
    $I->wait('#recordState')->hasClass('record');
    $I->click('#recordBar BUTTON.see');
    $I->select('#sentence', 'bit longer');
    $I->select('#sentence', 'it longer');
    #$I->select('#sentence', '');
    $I->click('#recordState');
    $I->wait('#recordState')->hasNotClass('record');
});

I::open($file);
$aCode = I::getRecord();
dump($aCode);

$code = preg_replace('~\s+~', ' ', join('', $aCode));
Is::contains("I::see('#sentence')", $code);
Is::contains("->hasText('bit longer')", $code);
Is::contains("->hasText('/it longer/')", $code);
