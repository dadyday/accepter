<?php
require_once 'bootstrap.php';

use Accepter\Accept as I;

$html = <<<HTML
<html>
<head>
    <style>
        body { color: black; }
        #bin-da:hover { color: red; }
    </style>
</head>
<body>
    <span id="bin-da" onclick="this.innerText = 'war da';">bin da</span>
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
file_put_contents(TEMP.'/record.php', $php);

include TEMP.'/record.php';
