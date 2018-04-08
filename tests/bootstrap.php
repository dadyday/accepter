<?php
require_once __DIR__.'/../vendor/autoload.php';

define('ROOT', __DIR__.'/../');
Tester\Environment::setup();
if (!getenv(Tester\Environment::RUNNER)) {
    Tracy\Debugger::enable();
    Tracy\Debugger::$maxDepth = 8;
    Tracy\Debugger::$maxLength = 500;

    define('TEMP', __DIR__.'/_temp/');
}
else {
    $thread = getenv(Tester\Environment::THREAD);
    define('TEMP', __DIR__."/_temp/$thread/");
};
Tester\Helpers::purge(TEMP);

function ws($text) {
    $text = strtr($text, ["\r" => '\r', "\n" => '\n', "\t" => '\t', ' ' => '_']); // "↲←↦•"
    return $text;
}

function loadCodeBlock($mdFile, $type, $no) {
    $content = file_get_contents($mdFile);
    $pattern = '~```'.$type.'([^`]*)```~s';
    if (preg_match_all($pattern, $content, $aMatches, PREG_SET_ORDER)
        && isset($aMatches[$no])) {
        return $aMatches[$no][1];
    }
    throw new \Exception("codeblock $type/$no not found in $mdFile");
}
