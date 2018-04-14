<?php
require_once __DIR__.'/../bootstrap.php';

use Tester\Assert as Is;
use Accepter\Accept as I;

$file = TEMP.'/demo.php';
$php = loadCodeblock(ROOT.'/readme.md', 'php', 0);
$php = str_replace('demo/deepthought.html', ROOT.'/demo/deepthought.html', $php);
file_put_contents($file, $php);


I::addDefaultListener('simulate', function($I) {
    $I->click('#recordBar .keys');
    $I->click('#question');
    $I->type('the question');
    $I->hit('enter');
    $I->click('#recordBar .wait');
    $I->wait('//li[text()=42]', 10)
        ->click();
});
I::addDefaultListener('simulate', function($I) {
    $I->click('#recordState');
    $I->wait('#recordState', 20)->hasNotClass('record');
});
include($file);


$should = loadCodeblock(ROOT.'/readme.md', 'php', 1);
$changed = file_get_contents($file);
dump($changed);

Is::match('~'.
    'I::open.*'.
    'I::focus.*'.
        'enter.*'.
    'I::wait.*'.
        'hasText.*'.
    'I::record.*'.
    '~s', $changed);

#Is::same($should, $changed);
