# Logger (v2.x)

SugiPHP Logger is a simple file logger. It is [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) compliant.

## Installation

```bash
composer require sugiphp/logger:2.*@dev
```

## Usage

```php
<?php

use SugiPHP\Logger\Logger;

$logger = new Logger();
$logger->debug("hello world");
$logger->info("context will be saved as JSON string", array("foo" => "bar"));
$logger->error("it happens");
?>
```

The output will be:
```
[2015-05-27 14:55:52] [debug] hello world
[2015-05-27 14:55:52] [info] context will be saved as JSON string {"foo":"bar"}
[2015-05-27 14:55:52] [error] it happens
```

Note that the example above will use standard error log (`error_log()`function) to write messages, since `filename` setting is not present.

## Specify file to write to

You can specify where to log files with `filename` setting or with `setFileName(string filename)` method. Here are 2 examples:

```php
<?php
// Using date in the filename
$logger = new Logger(
    "filename" => "/path/to/logs/".date("Y-m-d").".log"
);
// writes messages to the memory. It will be cleared after the script is over.
$logger->setFileName("php://memory");
// back to default error_log
$logger->setFileName();
?>
```

## Log Level Threshold

You can omit writing messages with log level below the threshold log level using `setLevel(string $level)` or by `level` options on construction. The default threshold level is `debug` meaning that every messages are logged.
If you set threshold level for example to `notice` no message with level `info` and `debug` will be logged.

### Available Log Levels

``` php
<?php
use Psr\Log\LogLevel;

// from highest to lowest priority
LogLevel::EMERGENCY = 'emergency'; // system is unusable
LogLevel::ALERT = 'alert';         // action must be taken immediately
LogLevel::CRITICAL = 'critical' ;  // critical conditions
LogLevel::ERROR = 'error';         // error conditions
LogLevel::WARNING = 'warning';     // warning conditions
LogLevel::NOTICE = 'notice';       // normal but significant condition
LogLevel::INFO = 'info';           // informational messages
LogLevel::DEBUG = 'debug';         // debug-level messages
?>
```


### Log Format

The `logFormat` option on instance creation and `setFormat(string $format)` method gives you ability to define what each log record will look like. It can contain parameters and static test. The format you set will be parsed for variables wrapped in curly braces like `{message}`, `{level}` and will replace them with appropriate values as follows:

| Parameter  | Description                       |
| ---------  | --------------------------------- |
| {datetime} | date/time in `dateFormat` format  |
| {level}    | PSR-3 log level                   |
| {LEVEL}    | log level in uppercase            |
| {message}  | text message                      |
| {context}  | JSON-encoded context              |

### Date Format

The default date format is `Y-m-d H:i:s`. You can change it using `dateFormat` option and/or `setDateFormat(string $format)` method.

### Line Endings

By default the Logger will add `\n` after each record. If you want to override this, you can set it with `eol` or use `setEol(string $eol)` method.

    "eol" => "\r\n"

## Extended Example

First example using all available options.

```php
<?php

use SugiPHP\Logger\Logger;

$config = array(
    "filename"   => "php://stdout",
    "level"      => "info",
    "logFormat"  => "{datetime} {LEVEL}: {message} {context}",
    "dateFormat" => "m/d/y",
    "eol"        => "|", // All messages will be saved on 1 line
);
$logger = new Logger($config);
$logger->debug("hello world");
$logger->info("context will be saved as JSON string", array("foo" => "bar"));
$logger->error("it happens");
?>
```

the output will:

```
05/27/15 INFO: context will be saved as JSON string {"foo":"bar"}|05/27/15 ERROR: it happens|
```

