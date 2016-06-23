
### *Maintained Fork*

This is a fork of [rase-/socket.io-php-emitter](https://github.com/rase-/socket.io-php-emitter).  
Since the original repo is not being maintained anymore and is having compatibility problems with 
newer versions of Socket.io, I recommend using this repo, and also submitting issues here.  

[@ashiina](https://github.com/ashiina)  

socket.io-php-emitter
=====================

A PHP implementation of socket.io-emitter.

This project requires a Redis client for PHP. If you dont have the [PECL Redis](https://github.com/nicolasff/phpredis) installed, the emitter will default to using [TinyRedisClient](https://github.com/ptrofimov/tinyredisclient). You can, however, pass in any Redis client that supports a `publish` method.

## Installation and development
To install and use in your PHP project, install it as a [composer package](https://packagist.org/packages/ashiina/socket.io-emitter). Install dependencies with `composer install`.

To run tests, invoke `make test`. The current test suite will just be checking redis monitor that everything is published correctly. Some work will be put into making a better integration test suite in the near future.

## Usage

### Initialization
```php
$redis = new \Redis(); // Using the Redis extension provided client
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
// Below initialization will create a  phpredis client, or a TinyRedisClient depending on what is installed
$emitter = new SocketIO\Emitter(array('port' => '6379', 'host' => '127.0.0.1'));
// broadcast can be replaced by any of the other flags
$emitter->broadcast->emit('other event', 'such data');
```

### Emitting objects
```php
$emitter = new SocketIO\Emitter(); // If arguments are not provided, they will default to array('port' => '6379', 'host' => '127.0.0.1')
$emitter->emit('event', array('property' => 'much value', 'another' => 'very object'));
```
