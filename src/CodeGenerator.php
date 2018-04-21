<?php
namespace Accepter;

use Exception;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class CodeGenerator {
    use SmartObject;
    use InvokeTrait;

    protected
        $oCfg = null,
        $aCode = [],
        $lastSelector;

    function __construct($cfg) {
        $this->oCfg = DataObject::from($cfg + [
            'prefix' => '\Accepter\Accept::',
            'indent' => 0,
            'tab' => '    ',
            'lf' => "\n",
            'commentOut' => false,
        ]);
        if (is_numeric($this->oCfg->indent)) {
            $this->oCfg->indent = str_repeat($this->oCfg->tab, $this->oCfg->indent);
        }
    }

    function reset() {
        $this->aCode = [];
        $this->lastSelector = null;
    }

    function runAll($aData) {
        foreach ($aData as $data) {
            $this->run($data);
        }
    }

    function run($data) {
        $data = DataObject::from($data, true);

        $selector = $this->getSelector($data->target);

        switch ($data->mode) {
            case 'see':
                $this->openCommand("see('$selector')", $selector);
                $this->addInspection($data->target);
                break;
            case 'wait':
                $this->openCommand("wait('$selector')", $selector);
                $this->addInspection($data->target);
                break;
            case 'find':
                $this->openCommand("find('$selector')", $selector);
                $this->addInspection($data->target);
                break;
            case 'mouse':
                $this->openCommand("see('$selector')", $selector);
                $this->addAction($data->type);
                break;
            case 'keys':
                $this->openCommand("focus('$selector')", $selector);
                if ($data->type == 'keydown') {
                    $this->addKeyAction($data->args);
                }
                else {
                    #$this->addAction($data->type);
                }
                break;
            default:
                throw new Exception("unknown data mode $data->mode");
        }
    }

    function getSelector($target) {
        if ($target->id) $selector = '#'.$target->id;
        else if ($target->class) $selector = '.'.$target->class;
        else if ($text = $this->getSelectionText($target)) {
            $selector = $text;
        }
        else if ($target->xpath) $selector = $target->xpath;

        return $selector;
    }

    function openCommand($code, $selector) {
        if ($selector !== $this->lastSelector) {
            $this->closeCommand();
            $this->aCode[] = $this->oCfg->prefix.$code;
        }
        $this->lastSelector = $selector;
    }

    function closeCommand() {
        if ($this->lastSelector) $this->aCode[count($this->aCode)-1] .= ';';
        $this->lastSelector = null;
    }

    function addInspection($target) {
        if ($text = $this->getSelectionText($target)) {
            $this->aCode[] = $this->oCfg->tab."->hasText('$text')";
        }
        if (isset($target->bold)) {
            $this->aCode[] = $this->oCfg->tab.($target->bold ? "->isBold()" : "->isNotBold()");
        };
        if ($target->class) {
            $this->aCode[] = $this->oCfg->tab."->hasClass('{$target->class}')";
        }
        #$this->aCode[] = $this->indent(1)."->hasColor('{$target->color}')";
    }

    function getSelectionText($target) {
        if (!$target->text) return null;
        if (empty($target->selection) || $target->selection->type !== 'range') return $target->text;

        $text = $target->selection->text;
        if (preg_match('~\b'.preg_quote($text).'\b~', $target->text)) return $text;
        return '/'.preg_quote($text).'/';
    }

    var $lastKey = '';

    function addKeyAction($oArgs) {
        $key = $oArgs->key;

        if (preg_match('~^[\x20-\xff]$~', $key)) {
            if ($this->lastKey) {
                $this->removeLastAction();
            }
            $this->lastKey .= $key;
            return $this->addAction('type', "'$this->lastKey'");
        }

        if ($key == 'Enter') {
            if ($this->lastKey) {
                $this->removeLastAction();
            }
            $ok = $this->addAction('enter', "'$this->lastKey'");
            $this->lastKey = '';
            return $ok;
        }

        if (in_array($key, ['Shift', 'Control', 'Alt', 'AltGraph'])) {
            return;
        }

        $this->lastKey = '';
        return $this->addAction('hit', "'$key'");
    }

    function addAction($type, $args = '') {
        $this->aCode[] = $this->oCfg->tab.'->'.$type.'('.$args.')';
    }

    function removeLastAction() {
        $this->aCode = array_slice($this->aCode, 0, -1);
    }

    function getCodeArray() {
        $aRet = [];
        if (!$this->aCode) return $aRet;

        $this->closeCommand();
        if ($this->oCfg->commentOut) {
            $aRet[] = $this->oCfg->indent.'/* recorded'.$this->oCfg->lf;
        }
        foreach($this->aCode as $l => $line) {
            $aRet[] = $this->oCfg->indent.$line.$this->oCfg->lf;
        }
        if ($this->oCfg->commentOut) {
            $aRet[] = $this->oCfg->indent.'//*/'.$this->oCfg->lf;
        }
        #bdump($aRet);
        return $aRet;
    }

    function getCode() {
        $aCode = $this->getCodeArray();
        return implode($aCode);
    }
}
