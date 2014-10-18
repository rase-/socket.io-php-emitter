socket.io-php-emitter
=====================

A PHP implementation of socket.io-emitter.

This project requires a Redis client for PHP. If you dont have the [PECL Redis](https://github.com/nicolasff/phpredis) installed already. This library comes with a built-in Redis client, initialize with host, port or socket to use it.

## Installation and development
To install and use in your PHP project, install it as a [composer package](https://packagist.org/packages/rase/socket.io-emitter).

To run tests, invoke `make test`. The current test suite will just be checking redis monitor that everything is published correctly. Some work will be put into making a better integration test suite in the near future.

## Usage

### Initializing using an existing Redis client
```php
$redis = new \Redis();
$redis->connect('127.0.0.1', '6379');
$emitter = new SocketIO\Emitter($redis);
$emitter->emit('event', 'payload str');
```
### Initialization using the built-in Redis client
#####Example #1
```php
$emitter = new SocketIO\Emitter(array('host' => 'localhost','port' => 6378));
```
#####Example #2 (using default port)
```php
$emitter = new SocketIO\Emitter(array('host' => 'localhost'));
```
#####Example #3 (using default host)
```php
$emitter = new SocketIO\Emitter(array('port' => 6378));
```
#####Example #4 (built-in Redis client)
```php
$redis = new SocketIO\Redis('localhost:6378');
$emitter = new SocketIO\Emitter($redis);
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