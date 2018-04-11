<?php
require_once __DIR__.'/../bootstrap.php';

use Tester\Assert as Is;
use Accepter\Accept as I;

$file = TEMP.'/waittest.html';
$html = <<<HTML
<body>
    <script>
        function start(el) {
            setTimeout(function() { el.innerText = 'Is there!'; }, 2000);
            setTimeout(function() { el.style.fontWeight = 'normal'; }, 4000);
            setTimeout(function() { document.body.appendChild(document.createElement('hr')); }, 2000);
        };
    </script>

    <h1 onclick="start(this)">will be changed soon ...</h1>
<body>
</html>
HTML;
file_put_contents($file, $html);

I::open($file);
I::see('<h1>')
    ->hasText('soon')
    ->click();

I::wait('<h1>', 5)
    ->hasText('there')
    ->isNotBold();

I::open($file);

Is::exception(function() {
    I::click('<h1>');
    I::wait('<h1>', 5)
        ->hasText('there')
        ->isNotBold()
        ->isNotVisible();
}, Tester\AssertException::class, '~timed out .* visible~');

I::open($file);
I::click('<h1>');
I::wait('<hr>', 5)
    ->isVisible();
