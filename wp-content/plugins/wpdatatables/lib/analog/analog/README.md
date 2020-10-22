# Analog - PHP micro logging package [![Build Status](https://travis-ci.org/jbroadway/analog.png)](https://travis-ci.org/jbroadway/analog)

* Copyright: (c) 2012-Present Johnny Broadway
* License: [MIT](http://www.opensource.org/licenses/mit-license.php)

<!-- a href='http://www.pledgie.com/campaigns/16595'><img alt='Click here to lend your support to: Analog and make a donation at www.pledgie.com !' src='http://www.pledgie.com/campaigns/16595.png?skin_name=chrome' border='0' /></a -->

A PHP logging package based on the idea of using closures
for configurability and extensibility. It functions as a static class, but you can
completely control the writing of log messages through a closure function
(aka [anonymous functions](http://ca3.php.net/manual/en/functions.anonymous.php)),
or use the `Analog\Logger` wrapper that implements the
[PSR-3 specification](https://www.php-fig.org/psr/psr-3/).

## Installation

Install the latest version with:

```bash
$ composer require jbroadway/analog
```

## Usage

### Basic Usage

```php
<?php

use Analog\Analog;
use Analog\Handler\FirePHP;

Analog::handler (FirePHP::init ());

Analog::log ('Take me to your browser');
```

### Usage with [PSR-3](https://www.php-fig.org/psr/psr-3/)

```php
<?php

use Analog\Logger;
use Analog\Handler\Variable;

$logger = new Logger;

$log = '';

$logger->handler (Variable::init ($log));

$logger->alert ('Things are really happening right now!');

var_dump ($log);
```

### Usage with a custom handler

```php
<?php

use Analog\Analog;

// Default logging to /tmp/analog.txt
Analog::log ('Log this error');

// Log to a MongoDB log collection
Analog::handler (function ($info) {
	static $conn = null;
	if (! $conn) {
		$conn = new Mongo ('localhost:27017');
	}
	$conn->mydb->log->insert ($info);
});

// Log an alert
Analog::log ('The sky is falling!', Analog::ALERT);

// Log some debug info
Analog::log ('Debugging info', Analog::DEBUG);
```

For more examples, see the [examples](https://github.com/jbroadway/analog/tree/master/examples) folder.

## Logging Options

By default, this class will write to a file named `sys_get_temp_dir() . '/analog.txt'`
using the format `"machine - date - level - message\n"`, making it usable with no
customization necessary.

Analog also comes with over a dozen pre-written handlers in the Analog/Handlers folder,
with examples for each in the examples folder. These include:

* Amon - Send logs to the [Amon](http://amon.cx/) server monitoring tool
* Apprise - Send notifications through the [apprise](https://github.com/caronc/apprise) command line tool
* Buffer - Buffer messages to send all at once (works with File, Mail, Stderr, and Variable handlers)
* ChromeLogger - Sends messages to [Chrome Logger](http://craig.is/writing/chrome-logger) browser plugin
* File - Append messages to a file
* FirePHP - Send messages to [FirePHP](http://www.firephp.org/) browser plugin
* GELF - Send message to the [Graylog2](http://www.graylog2.org/) log management server
* Ignore - Do nothing
* LevelBuffer - Buffer messages and send only if sufficient error level reached
* LevelName - Convert log level numbers to names in log output
* Mail - Send email notices
* Mongo - Save to MongoDB collection
* Multi - Send different log levels to different handlers
* PDO - Send messages to any PDO database connection (MySQL, SQLite, PostgreSQL, etc.)
* Post - Send messages over HTTP POST to another machine
* Slackbot - Post messages to Slack via Slackbot
* Stderr - Send messages to STDERR
* Syslog - Send messages to syslog
* Threshold - Only writes log messages above a certain threshold
* Variable - Buffer messages to a variable reference.

So while it's a micro class, it's highly extensible and very capable out of the box too.

## Rationale

I wrote this because I wanted something very small and simple like
[KLogger](https://github.com/katzgrau/KLogger), and preferably not torn out
of a wider framework if possible. After searching, I wasn't happy with the
single-purpose libraries I found. With KLogger for example, I didn't want an
object instance but rather a static class, and I wanted more flexibility in
the back-end.

I also found some that had the flexibility also had more complexity, for example
[Monolog](https://github.com/Seldaek/monolog) is dozens of source files (not incl. tests).
With closures, this seemed to be a good balance of small without sacrificing
flexibility.

> What about Analog, the logfile analyzer? Well, since it hasn't been updated
> since 2004, I think it's safe to call a single-file PHP logging class the
> same thing without it being considered stepping on toes :)
