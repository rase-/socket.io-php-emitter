socket.io-php-emitter
=====================

A PHP implementation of socket.io-emitter.

This project uses [msgpack-php](https://github.com/msgpack/msgpack-php) and [phpredis](https://github.com/nicolasff/phpredis).
Make sure to have phpredis installed before trying to use the emitter. If you don't have it installed, you can include [CRedis](https://github.com/colinmollenhour/credis) library in your project.

## Installation and development
To install and use in your PHP project, install it as a [composer package](https://packagist.org/packages/rase/socket.io-emitter).

To run tests, invoke `make test`. The current test suite will just be checking redis monitor that everything is published correctly. Some work will be put into making a better integration test suite in the near future.

## Usage

### Using an existing redis instance
```php
$redis = new \Redis();
$redis->connect('127.0.0.1', '6379');
$emitter = new SocketIO\Emitter($redis);
$emitter->emit('event', 'payload str');
```

### Emitting without manually creating a redis instance
```php
$emitter = new SocketIO\Emitter(array('port' => '6379', 'host' => '127.0.0.1'));
$emitter->emit('event', 'wow');
```

### Broadcasting and other flags
Possible flags
* json
* volatile
* broadcast

```php
$emitter = new SocketIO\Emitter(array('port' => '6379', 'host' => '127.0.0.1'));
// broadcast can be replaced by any of the other flags
$emitter->broadcast->emit('other event', 'such data');
```

### Emitting objects
```php
$emitter = new SocketIO\Emitter(array('port' => '6379', 'host' => '127.0.0.1'));
$emitter->emit('event', array('property' => 'much value', 'another' => 'very object'));
```
