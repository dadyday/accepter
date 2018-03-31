<?php
require_once 'bootstrap.php';

use Tester\Assert as Is;
use Accepter\CodeGenerator as Gen;

$data = [
    'mode' => 'inspect',
    'target' => [
        'id' => 'myId',
        'text' => 'hello world'
    ]
];

$oGen = new Gen([
    'prefix' => 'I::',
    'indent' => 1,
    'tab' => '    ',
    'lf' => '
',
], $data);
$code = $oGen->getCode();
$should = <<<PHP
    I::see('#myId')
        ->hasText('hello world')
        ->isNotBold();

PHP;
#bdump([ws($should), ws($code)]);
Is::same(ws($should), ws($code));
