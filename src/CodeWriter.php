<?php
namespace Accepter;

use Exception;
use Nette\SmartObject;

/**
 *  @property int $offset
 */
class CodeWriter {
    use SmartObject;


    static $aOffset = [];

    protected
        $file,
        $line,
        $aContent = null;

    function __construct() {
    }

    function findTarget($entryClass) {
        list($file, $line) = $this->_getTraceEntry($entryClass);
        if (!$file) throw new Exception('caller not found');

        $file = preg_replace('~\\\\~', '/', $file);
        $file = preg_replace('~\'~', '\\\'', $file);

        $this->file = $file;
        $this->line = $line;
        $this->aContent = file($file, FILE_IGNORE_NEW_LINES);
    }

    function getCodePrefix($marker) {
        $pattern = '~^(.*)'.preg_quote($marker).'~';
        $line = $this->aContent[$this->line + $this->offset];
        if (!preg_match($pattern, $line, $aMatch)) return null;
        return $aMatch[1];
    }

    function addCode($code) {
        if (empty($this->file)) throw new Exception('file empty while putting recorded lines');

        $line = $this->line + $this->offset;
        $this->offset += count($code);

        array_splice($this->aContent, $line, 0, $code);
    }

    function backup() {
        $bak = dirname($this->file).'/_'.basename($this->file).'.bak';
        $code = $this->_attachLineFeed($this->aContent);
        file_put_contents($bak, implode($code));
    }

    function save() {
        $code = $this->_attachLineFeed($this->aContent);
        file_put_contents($this->file, implode($code));
    }

    protected function getOffset() {
        return isset(static::$aOffset[$this->file]) ? static::$aOffset[$this->file] : 0;
    }

    protected function setOffset($value) {
        static::$aOffset[$this->file] = $value;
    }

    protected function _attachLineFeed($aCode, $nl = "\n") {
        return array_map(function($item) use($nl) { return $item.$nl; }, $aCode);
    }

    protected function _getTraceEntry($entryClass) {
        $aTrace = debug_backtrace();
        #bdump($aTrace);
        for ($n = count($aTrace)-1; $n > 0 ; $n--) {
            if (!isset($aTrace[$n]['file'])) continue;
            if (!isset($aTrace[$n]['class'])) continue;
            if ($aTrace[$n]['class'] == $entryClass) {
                break;
            }
        };

        if ($n <= 0) return [null, null];
        return [$aTrace[$n]['file'], $aTrace[$n]['line']-1];
    }
}
