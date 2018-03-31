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
        $mode,
        $type,
        $target;

    function __construct($cfg, $data) {
        $this->oCfg = DataObject::from($cfg + [
            'prefix' => '\Accepter\Accept::',
            'indent' => 0,
            'tab' => '    ',
            'lf' => "\n",
        ]);
        if (is_numeric($this->oCfg->indent)) {
            $this->oCfg->indent = str_repeat($this->oCfg->tab, $this->oCfg->indent);
        }

        $data = DataObject::from($data);
        bdump($data);
        $this->mode = $data->mode;
        $this->type = $data->type;
        $this->target = $data->target;
    }

    function locateElement() {
        if ($this->target->id) $selector = '#'.$this->target->id;
        else if ($this->target->class) $selector = '.'.$this->target->class;
        else if ($this->target->text) $selector = $this->target->text;
        else if ($this->target->xpath) $selector = $this->target->xpath;

        return $selector;
    }

    function indent($times = 0) {
        return str_repeat($this->oCfg->tab, $this->oCfg->indent + $times);
    }

    function getCodeArray() {
        $el = $this->locateElement();
        if (!$el) return [];

        $aCode[] = "{$this->oCfg->prefix}see('$el')";

        switch ($this->mode) {
            case 'inspect':
                if ($this->target->text) {
                    $aCode[] = $this->oCfg->tab."->hasText('{$this->target->text}')";
                    $aCode[] = $this->oCfg->tab.($this->target->bold ? "->isBold()" : "->isNotBold()");
                };
                if ($this->target->class) {
                    $aCode[] = $this->oCfg->tab."->hasClass('{$this->target->class}')";
                }
                #$aCode[] = $this->indent(1)."->hasColor('{$this->target->color}')";
                break;
            case 'mouse':
                $aCode[] = $this->oCfg->tab.'->'.$this->type.'()';
                break;
            case 'keys':
                $aCode[] = $this->oCfg->tab.'->'.$this->type.'('.$this->args.')';
                break;
        }
        $aCode[count($aCode)-1] .= ';';

        foreach($aCode as $l => $line) $aCode[$l] = $this->oCfg->indent.$line.$this->oCfg->lf;
        bdump($aCode);
        return $aCode;
    }

    function getCode() {
        $aCode = $this->getCodeArray();
        return implode($aCode);
    }
}
