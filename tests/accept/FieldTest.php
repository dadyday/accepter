<?php
require_once __DIR__.'/../bootstrap.php';

use Tester\Assert as Is;
use Accepter\Accept as I;

class FieldTest extends Tester\TestCase {

    function testInput() {
        $this->prepare('
            <input id="theId" class="aClass" name="theName" value="aValue">
        ');
        $el1 = I::see('<input>')
            ->isTag('input')
            ->hasId('theId')
            ->hasClass('aClass')
            ->hasName('theName')
            ->hasValue('aValue')
        ;

        $el2 = I::see('theId');
        Is::equal($el1, $el2);

        $el2 = I::see('aClass');
        Is::equal($el1, $el2);

        $el2 = I::see('theName');
        Is::equal($el1, $el2);

        $el2 = I::see('aValue');
        Is::equal($el1, $el2);
    }

    function testSelect() {
        $this->prepare('
            <select id="theId" class="aClass" name="theName">
                <option value="aValue">First Value
                <option value="anotherValue" selected>Second Value
            </select>
        ');
        $el1 = I::see('<select>')
            ->isTag('select')
            ->hasName('theName')
            ->hasNotValue('aValue')
            ->hasValue('anotherValue')
            ->hasNotValue('First')
            ->hasValue('Second')
            ->hasText('First')
            ->hasText('Second')
        ;

        $el2 = I::see('theId');
        Is::equal($el1, $el2);

        $el2 = I::see('aClass');
        Is::equal($el1, $el2);

        $el2 = I::see('theName');
        Is::equal($el1, $el2);


        I::see('anotherValue')
            ->isTag('option')
            ->hasValue('anotherValue')
            ->hasText('Second')
            ->isSelected()
        ;

        I::see('aValue')
            ->isTag('option')
            ->hasValue('aValue')
            ->hasText('First')
            ->isNotSelected()
        ;

    }

    function testLabel() {
        $this->prepare('
            <label for="theId">aLabel</label>
            <input id="theId" name="theName">
            <label>
                anotherLabel
                <input name="anotherName">
            </label>
        ');

        I::see('aLabel')
            ->labelFor()
            ->isTag('input')
            ->hasName('theName')
        ;

        I::see('anotherLabel')
            ->labelFor()
            ->isTag('input')
            ->hasName('anotherName')
        ;

    }

    protected function prepare($html) {
        $file = TEMP.'/fieldtest.html';
        $html = "<body>$html</body>";
        file_put_contents($file, $html);

        I::open($file);
    }
}

(new FieldTest)->run();
