# Warning: this project has been deprecated

Since v0.5 of ReactPHP [event-loop](https://github.com/reactphp/event-loop) offers native [support for signal handling](https://github.com/reactphp/event-loop/tree/v0.5.0#addsignal).

# React-PCNTL

[![Build Status](https://secure.travis-ci.org/mkraemer/react-pcntl.png)](http://travis-ci.org/mkraemer/react-pcntl)
[![Coverage Status](https://coveralls.io/repos/github/mkraemer/react-pcntl/badge.svg?branch=test-coverage-coveralls)](https://coveralls.io/github/mkraemer/react-pcntl?branch=test-coverage-coveralls)

Basic PCNTL bindings for [React PHP](https://github.com/reactphp).

## Install

The best way to install this library is through [composer](http://getcomposer.org):

```JSON
{
    "require": {
        "mkraemer/react-pcntl": "^3.0.*"
    }
}
```

This library depends on the [PCNTL extension](http://www.php.net/manual/en/book.pcntl.php).
**Note:** version 2 of this library requires PHP >= 5.4. If you are using PHP 5.3, use the `1.0.*` version:

```JSON
{
    "require": {
        "mkraemer/react-pcntl": "1.0.*"
    }
}
```
## Usage

This library provides the PCNTL class which taskes an event loop and optionally the timer interval in which the PCNTL signals should be read as constructor arguments.
After initializing the class, you can use the on() method to register event listeners to PCNTL signals.

```php
$loop = React\EventLoop\Factory::create();
$pcntl = new MKraemer\ReactPCNTL\PCNTL($loop);

$pcntl->on(SIGTERM, function () {
    // Clear some queue
    // Write syslog
    // Do ALL the stuff
    echo 'Bye'.PHP_EOL;
    die();
});

$pcntl->on(SIGINT, function () {
    echo 'Terminated by console'.PHP_EOL;
    die();
});

echo 'Started as PID '.getmypid().PHP_EOL;
$loop->run();

```

