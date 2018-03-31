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

    public
        $tab = '    ',
        $lf = "\n";

    function __construct() {
    }

    function findTarget($entryClass) {
        list($file, $line) = $this->_getTraceEntry($entryClass);
        if (!$file) throw new Exception('caller not found');

        $file = preg_replace('~\\\\~', '/', $file);
        $file = preg_replace('~\'~', '\\\'', $file);

        $this->attachFile($file, $line);
    }

    function attachFile($file, $line = null) {
        $content = file($file);
        $this->attachContent($content, $file, $line);
    }

    function attachContent($content, $file = null, $line = null) {
        $this->file = $file;
        $this->line = $line;
        $this->aContent = is_array($content) ? $content : explode("\n", $content);
        $this->_scanCodeStyle();
    }

    protected function _scanCodeStyle() {
        $this->tab = '    ';
        $this->lf = "\n";
        foreach ($this->aContent as $line) {
            if (!preg_match('~^(\t| +|)[^\r\n]*?([\r\n]+)$~', $line, $aMatch)) continue;
            list(, $tab, $lf) = $aMatch;
            $this->lf = $lf;
            if ($tab) {
                $this->tab = $tab;
                break;
            }
        }
    }

    function getPrefix($marker, &$indent) {
        $pattern = '~^(\s*)(.*)'.preg_quote($marker).'~';
        $line = $this->aContent[$this->line + $this->offset];
        #bdump([$pattern, $line], 'prefix');
        if (!preg_match($pattern, $line, $aMatch)) return null;
        $indent = $aMatch[1];
        return $aMatch[2];
    }

    function addCode($aCode) {
        if (empty($this->file)) throw new Exception('file empty while putting recorded lines');

        $line = $this->line + $this->offset;
        $this->offset += count($aCode);

        array_splice($this->aContent, $line, 0, $aCode);
    }

    function backup() {
        $bak = dirname($this->file).'/_'.basename($this->file).'.bak';
        file_put_contents($bak, implode($this->aContent));
    }

    function save() {
        file_put_contents($this->file, implode($this->aContent));
    }

    protected function getOffset() {
        return isset(static::$aOffset[$this->file]) ? static::$aOffset[$this->file] : 0;
    }

    protected function setOffset($value) {
        static::$aOffset[$this->file] = $value;
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
