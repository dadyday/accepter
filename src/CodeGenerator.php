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
        ]);
        if (is_numeric($this->oCfg->indent)) {
            $this->oCfg->indent = str_repeat($this->oCfg->tab, $this->oCfg->indent);
        }
    }

    function runAll($aData) {
        foreach ($aData as $data) {
            $this->run($data);
        }
    }

    function run($data) {
        $data = DataObject::from($data);
        bdump($data);

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
            case 'keys':
                $this->openCommand("see('$selector')", $selector);
                $this->addAction($data->type, $data->args);
                break;
            default:
                throw new Exception("unknown data mode $data->mode");
        }
    }

    function getSelector($target) {
        if ($target->id) $selector = '#'.$target->id;
        else if ($target->class) $selector = '.'.$target->class;
        else if ($target->text) $selector = $target->text;
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
        if ($target->text) {
            $this->aCode[] = $this->oCfg->tab."->hasText('{$target->text}')";
            $this->aCode[] = $this->oCfg->tab.($target->bold ? "->isBold()" : "->isNotBold()");
        };
        if ($target->class) {
            $this->aCode[] = $this->oCfg->tab."->hasClass('{$target->class}')";
        }
        #$this->aCode[] = $this->indent(1)."->hasColor('{$target->color}')";
    }

    function addAction($type, $args) {
        $this->aCode[] = $this->oCfg->tab.'->'.$type.'('.$args.')';
    }

    function getCodeArray() {
        $aRet = [];
        $this->closeCommand();
        foreach($this->aCode as $l => $line) {
            $aRet[$l] = $this->oCfg->indent.$line.$this->oCfg->lf;
        }
        bdump($aRet);
        return $aRet;
    }

    function getCode() {
        $aCode = $this->getCodeArray();
        return implode($aCode);
    }
}
