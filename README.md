socket.io-php-emitter
=====================

A PHP implementation of socket.io-emitter.

This project uses [msgpack-php](https://github.com/msgpack/msgpack-php) and [phpredis](https://github.com/nicolasff/phpredis). Make sure to have those extensions in use before trying to use the emitter.

## Usage

```php
<?php

// Emitting with a created redis instance
$redis = new Redis();
$redis->connect('127.0.0.1', '6379');
$emitter = new Emitter($redis);
$emitter->emit('event', 'payload str');

// Emitting without manually creating a redis instance
$emitter = new Emitter(NULL, array('port' => '6379', 'host' => '127.0.0.1'));
$emitter->emit('event', 'wow');

// Broadcasting
$emitter->broadcast();
$emitter->emit('other event', 'such data');

// Emitting binary
$emitter->binary();
$binarydata = pack("nvc*", 0x1234, 0x5678, 65, 66);
$emitter->emit('very', $binarydata);

?>
```
