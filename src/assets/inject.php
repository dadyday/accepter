<?php
$assets = __DIR__.'/';
function jsFormat($text) {
    $text = addslashes($text);
    $text = preg_replace('~</script>~', '</"+"script>', $text);
    $text = preg_replace('~([\r\n]+)~', "\\\r\n", $text);
    return $text;
}

$jquery = jsFormat(file_get_contents('https://code.jquery.com/jquery-3.3.1.slim.min.js'));
$css = jsFormat(file_get_contents($assets.'bar.css'));
$html = jsFormat(file_get_contents($assets.'bar.html'));
$js = (file_get_contents($assets.'record.js'));

return <<<JS
eval("$jquery");
var range = document.createRange();
var c = range.createContextualFragment("<style>$css</style>$html");
document.body.appendChild(c);
$js
JS;
