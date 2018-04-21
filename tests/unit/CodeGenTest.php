<?php
require_once __DIR__.'/../bootstrap.php';

use Tester\Assert as Is;
use Accepter\CodeGenerator as Gen;

$oGen = new Gen([
    'prefix' => 'I::',
    'indent' => 1,
    'tab' => '    ',
    'lf' => '
',
]);

$oGen->run([
    'mode' => 'see',
    'target' => [
        'id' => 'myId',
        'text' => 'hello world'
    ]
]);
$code = $oGen->getCode();
$should = <<<PHP
    I::see('#myId')
        ->hasText('hello world');

PHP;
#dump([ws($should), ws($code)]);
Is::same(ws($should), ws($code));
