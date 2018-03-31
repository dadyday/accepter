<?php
require_once __DIR__.'/../vendor/autoload.php';

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
