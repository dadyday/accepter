# Accepter

This is my little project for easy creating acceptance tests. Actually I'm working on it, so this is'nt really ready for usage yet.

## What it does

It runs your acceptance test script against a webdriver, simulates your defined interactions and checks your expectations. not new so far.

The difference to all the other acceptance tester is: it helps you interactively to create your test script.

This looks like this: You create a new test:
```php
<?php
use Accepter\Accept as I;

I::open('demo/deepthought.html');

I::record();
```

See the example in the demo folder.

Now if you run the accepter, it will open a browser window with a small toolbar.

![Sample recording](docs/images/rec.gif)

You can use the toolbar to select the elements on the page and choose what you want to inspect. If you are ready, the accepter will add these actions to your script. e.g:

```php
<?php
use Accepter\Accept as I;

I::open('demo/deepthought.html');

//* recorded 2018-04-01 11:00:00
I::focus('#question')
    ->enter('the question');

I::wait('#answers li', 10)
    ->isVisible()
    ->hasText('42')
    ->isBold();
// recorded */

I::record();
```

Run the script again, and all your actions and assert will be automated done. If somewhat fails, the browser stops and let you inspect what was wrong.

Nice, huh?

## How it works

The Tool uses a webdriver like selenium or chromedriver for the automation. On every page which will be loaded, it injects a little javascipt, that helps you to record your interactions. A codegenerator creates out of this data the scriptcode and writes it back into the calling file.

It shoud be possible, to create various other codegenerators for other acceptance test programs or languages.

## Conclusion

As said, this project is currently in progress, and not everything is working at this time. But if you like what you see, leave me a star, so i will keep coding ... :)
