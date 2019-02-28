# Laravel InfluxDB

[![Latest Version](https://img.shields.io/packagist/v/oanhnn/laravel-influxdb.svg)](https://packagist.org/packages/oanhnn/laravel-influxdb)
[![Software License](https://img.shields.io/github/license/oanhnn/laravel-influxdb.svg)](LICENSE)
[![Build Status](https://img.shields.io/travis/oanhnn/laravel-influxdb/master.svg)](https://travis-ci.org/oanhnn/laravel-influxdb)
[![Coverage Status](https://img.shields.io/coveralls/github/oanhnn/laravel-influxdb/master.svg)](https://coveralls.io/github/oanhnn/laravel-influxdb?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/oanhnn/laravel-influxdb.svg)](https://packagist.org/packages/oanhnn/laravel-influxdb)
[![Requires PHP](https://img.shields.io/travis/php-v/oanhnn/laravel-influxdb.svg)](https://travis-ci.org/oanhnn/laravel-influxdb)

A service made to provide, set up and use the library [influxdb-php](https://github.com/influxdata/influxdb-php/) in Laravel.

## TODO

- [x] Make repository on [Github](https://github.com)
- [x] Make repository on [Travis](https://travis.org)
- [x] Make repository on [Coveralls](https://coveralls.io)
- [x] Make repository on [Packagist](https://packagist.org)
- [x] Write logic classes
- [ ] Write test scripts
- [x] Write README & documents

## Requirements

* php >=7.1.3
* Laravel 5.5+
* influxdb-php 1.14.7+

## Installation

Begin by pulling in the package through Composer.

```bash
$ composer require oanhnn/laravel-influxdb
```

## Usage

### Reading Data

```php
<?php

// executing a query will yield a resultset object
$result = InfluxDB::query('select * from test_metric LIMIT 5');

// get the points from the resultset yields an array
$points = $result->getPoints();
```

### Writing Data

```php
<?php

// create an array of points
$points = array(
    new InfluxDB\Point(
        'test_metric', // name of the measurement
        null, // the measurement value
        ['host' => 'server01', 'region' => 'us-west'], // optional tags
        ['cpucount' => 10], // optional additional fields
        time() // Time precision has to be set to seconds!
    ),
    new InfluxDB\Point(
        'test_metric', // name of the measurement
        null, // the measurement value
        ['host' => 'server01', 'region' => 'us-west'], // optional tags
        ['cpucount' => 10], // optional additional fields
        time() // Time precision has to be set to seconds!
    )
);

$result = InfluxDB::writePoints($points, \InfluxDB\Database::PRECISION_SECONDS);
```

### Logging

> **NOTE** This feature is available on Laravel version 5.6+.

In `config/logging.php` file, config you log with driver `influxdb`

```php
<?php
return [
    'channels' => [
        // ...
        'custom' => [
            'driver' => 'influxdb',
            'name'   => 'channel-name',
            'level'  => 'info',
            'bubble' => true,
        ],
        // ...
	],
];

```

In your code using

```php
Log::channel('custom')->info('Some message');

```


## Changelog

See all change logs in [CHANGELOG](CHANGELOG.md)

## Testing

```bash
$ git clone git@github.com/oanhnn/laravel-influxdb.git /path
$ cd /path
$ composer install
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email to [Oanh Nguyen](mailto:oanhnn.bk@gmail.com) instead of 
using the issue tracker.

## Credits

- [Oanh Nguyen](https://github.com/oanhnn)
- [All Contributors](../../contributors)

## License

This project is released under the MIT License.   
Copyright © 2019 [Oanh Nguyen](https://oanhnn.github.io/).
