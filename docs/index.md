# Accepter

Accepter is a tool for creating and running acceptance tests. It uses a webdriver to automate your webapp and checks the result of your defined interactions.

## Get started

Install accepter and run the webdriver:
```
$ composer require --dev dadyday/accepter
$ java -jar selenium-server-standalone.jar
```

Create a test in your tests/ folder:
```php
<?php
require_once __DIR__.'/../vendor/autoload.php';

use Accepter\Accept as I;

I::open('http://localhost:8000');
I::see('<a>')
    ->hasText('click me')
    ->click();
# ... futher interactions and/or expectations here
```
Run the test:
```
$ tester tests
```

## Control Funcs

There are some funcs, to control your browser itself.

* `I::open(url)`
    opens a website, and waits until it is loaded. Redirects will be processed too.
    This should be called in the first lines of your test, because otherwise not very much of the following funcs will work.

* `I::reload()`
    reloads the current url, like as you click the browsers refresh button.

* `I::goBack([steps])`
    simulates a click on the browsers history-back button. The optional steps chooses a entry in your history list. e.g `I::goBack(2)` opens the penultimate url.

* `I::goForward([steps])`
    as opposite of goBack this func opens following pages, if they exist.

## Page Helper

* `I::waitPage([seconds])`
    waits until the main document is fully loaded. That is useful after clicks, that navigate to other pages.

* `I::waitAjax([seconds])`
    waits for all background ajax requests, and returns when all of them are finished.

* `I::waitLoaded({seconds})`
    waits for both page loading and ajax requests. This func is recommented, so your tests become independed from your page architecture.

* `I::waitScript({seconds})`
    waits for long running javascript functions. This implies page loading and ajax requests too.

## Selectors

### Selector Funcs

Selector funcs are used to find one or more web elements on your page. They all exspect a string that tries to identify them.

* `I::find(selector)`
    selects a list of elements, that matches the given selector string (see below)

* `I::wait(selector, seconds)`
    same as `find`, but waits a given amount of seconds to become present. You can add here conditions and interaction too. The func will wait for every condition until it becomes true and the overall wait time is not elapsed. e.g
```php
I::wait('#flicker', 5)
    ->isVisible() // immediately
    ->click() // immediately
    ->isNotVisible() // waits until hidden
    ->isVisible(); // waits until shown
```

* `I::see(selector)`
    selects a list of only visible elements

There are also selectors, which checks the absence of a defined element.

* `I::dontSee(selector)`
    selects a list like above, but checks on the end (on destructing) that the list is empty.

### Selector Strings

You can select elements with different methods depending on the string syntax.

* `'#myId'`
    finds element(s) with the given id

* `'<div>'`
    searchs for a tag name. `'<div class="myClass">'` searchs for tags with the given attributes

* `'//div[@class*=myClass]'`
    one or two slashes indicates a xpath search string.

* `'div .myClass > #myId'`
    if you are famillar with CSS, you may use those selector style

* <a name="element-text"></a>`'element text'`
    searches for element text, text inside childs or form field values. This is most useful to describe in your tests what you exspect to "see". But keep in mind that this method can deliver many elements, so that your acceptance test will always be true. You should add some Element conditions.

    The given text will be searched as whole caseinsensitive words and spaces will be expanded to one or more whitespaces ("the text" finds nodes with "the  \n<b>Text</b>", but not "the texter")

* <a name="regexp"></a>`'/regexp/'`
    searches also in text and values, but more specific with a regular expression.

### Element Contitions

Every selector returns a list of one or more webelements. You can check these lists against additional conditions. If a condition fails, the element will be removed from the list. Only if the last element doesn't match, the assertion will fail.

* `->hasTagName(tag)`, `->isTag(tag)`
    removes all nodes, that are not of these tag.

* `->hasText(search)`
    approves that every element in the list contains the given text fragment or value (see above, same as ['element text'](#element-text) or ['/regexp/'](#regexp))

* `->hasAttribute(name, [value])`
    checks a attribute value or only its present.

* `->hasId(id)`
    checks the id of the element.

* `->hasValue(value)`
    checks the value of field elements or options.

* `->hasClass(class)`
    checks if the css classes contain a specific class.

* `->hasStyle(property, [value])`
    checks if a style property is set or has a value.
    CSS Class styles will be inspected too.

* `->hasColor(color)`
    succeeds if text or background color matches
    (see [Colors](#colors))

* `->isBold()`

* `->isDecorated()`    

* `->isHighlited()`    

Every Condition has an opposite with a scheme of
`->hasNot*()` or `->hasNo*()` resp. `->isNot*()`. eg.
```php
I::see(':input')
    ->isNotBold()
    ->hasNoValue();
```

## Interactions

You can interact with your webapp with a handful functions, that will simulate the mouse or keyboard.

They can be called as method of the Accepter, with an additional selector for the specific element, or as method of the element list without the first argument. e.g

```php
I::click('#myLink');
I::see('#myLink')
    ->click();
```

Every interaction checks the element list, if it contains only one element. Otherwise an ambiguous exception will be thrown.

* `click([selector], [x, y])`
    simulates a click on a selected element. The optional coordinates are relative to the elements top left corner.

* `doubleClick([selector], [x, y])`
    like click method. Keep in mind, that two click events will be sent to the element, followed by a dblclick event, like in real situations.

* `rightClick([selector], [x, y])`
    like click method, with the right mouse button

* `hover([selector], [x, y])`
    moves only the mouse over the element

* `select([selector], [substring] | [from], [to])`
    selects the given substring or the whole element text, beginning by from-th resp. first charakter until to-th or last charakter. a negative argument will be counted back from the end of the string.

* `focus([selector])`
    sets the keyboard focus to a input element. the most keyboard interactions below are implicit calling this method, if the specified element hasn't the focus.

* `blur([selector])`
    causes the element loose its focus, with the effect that onchange events are triggered if the value was changed.

* `type([selector], text)`
    sends a string like typed over the keyboard

* `enter([selector], text)`
    like above, but sends a blur after that.

* `hit([selector], key)`
    simulates one or more key strokes. see [Keys](keys.md) for its names.

* `hold([selector], key, [seconds])`
    like above, but holds all keys pushed. if time is given, they will be automatic released.

* `release([selector], [key])`
    releases the given keys, if holded resp. all holded keys if nothing was given.
