<?php
require_once 'bootstrap.php';

use Tester\Assert as Is;
use Accepter\CodeWriter;

$php = "test\n\ntest2\n";
$oWriter = new CodeWriter();
$oWriter->attachContent($php);
Is::same(ws('    '), ws($oWriter->tab));
Is::same(ws("\n"), ws($oWriter->lf));



$file = TEMP.'/target.php';
$php = <<<'PHP'
<?php
require_once __DIR__.'/../bootstrap.php';
use Accepter\Accept as I;

I::open(__DIR__.'/testweb.html');

I::record();

PHP;
file_put_contents($file, $php);

$oWriter = new CodeWriter();
$oWriter->attachFile($file, 6);

Is::same(ws('    '), ws($oWriter->tab));
Is::same(ws("
"), ws($oWriter->lf));

$prefix = $oWriter->getPrefix("record(", $indent);
Is::same('I::', $prefix);
Is::same('', $indent);

$aCode = [
    "I::see('something')\r\n",
    "    ->click();\r\n",
];

$oWriter->addCode($aCode);
$oWriter->save();

$code = file_get_contents($file);
$should = <<<'PHP'
<?php
require_once __DIR__.'/../bootstrap.php';
use Accepter\Accept as I;

I::open(__DIR__.'/testweb.html');

I::see('something')
    ->click();
I::record();

PHP;

Is::contains(($should), ($code));
