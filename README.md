# React-PCNTL

Basic PCNTL bindings for [React PHP](https://github.com/reactphp).

##Install
This library requires PHP5.3 and the [PCNTL extension](http://www.php.net/manual/en/book.pcntl.php).
The best way to install this library is through [composer](http://getcomposer.org):

```JSON
{
    "require": {
        "mkraemer/react-pcntl": "dev-master"
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
