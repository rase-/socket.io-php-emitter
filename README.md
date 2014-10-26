socket.io-php-emitter
=====================

A PHP implementation of socket.io-emitter.

This project requires a Redis client for PHP. If you dont have the [PECL Redis](https://github.com/nicolasff/phpredis) installed already. You can use e.g. [Credis](https://github.com/colinmollenhour/credis), but any other client that implements a `publish` method should work.

## Installation and development
To install and use in your PHP project, install it as a [composer package](https://packagist.org/packages/rase/socket.io-emitter).

To run tests, invoke `make test`. The current test suite will just be checking redis monitor that everything is published correctly. Some work will be put into making a better integration test suite in the near future.

## Usage

### Initialization
```php
$redis = new \Redis();
$redis->connect('127.0.0.1', '6379');
$emitter = new SocketIO\Emitter($redis);
$emitter->emit('event', 'payload str');
```

### Broadcasting and other flags
Possible flags
* json
* volatile
* broadcast

```php
// Below initialization will create a  phpredis client, or throw an exception is phpredis is not installed
$emitter = new SocketIO\Emitter(array('port' => '6379', 'host' => '127.0.0.1'));
// broadcast can be replaced by any of the other flags
$emitter->broadcast->emit('other event', 'such data');
```

### Emitting objects
```php
$emitter = new SocketIO\Emitter(array('port' => '6379', 'host' => '127.0.0.1'));
$emitter->emit('event', array('property' => 'much value', 'another' => 'very object'));
```
