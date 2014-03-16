socket.io-php-emitter
=====================

A PHP implementation of socket.io-emitter.

This project uses [msgpack-php](https://github.com/msgpack/msgpack-php) and [phpredis](https://github.com/nicolasff/phpredis). Make sure to have those extensions in use before trying to use the emitter.

## Installation and development
To install and use in your PHP project, just use this as a composer package (will be available soon).

To run tests, invoke `make test`.

## Usage

### Using an existing redis instance
```php
<?php

$redis = new \Redis();
$redis->connect('127.0.0.1', '6379');
$emitter = new SocketIO\Emitter($redis);
$emitter->emit('event', 'payload str');

?>
```

### Emitting without manually creating a redis instance
```php
<?php

$emitter = new SocketIO\Emitter(array('port' => '6379', 'host' => '127.0.0.1'));
$emitter->emit('event', 'wow');

?>
```

### Broadcasting and other flags
Possible flags
* json
* volatile
* broadcast

```php
<?php

$emitter = new SocketIO\Emitter(array('port' => '6379', 'host' => '127.0.0.1'));
// broadcast can be replaced by any of the other flags
$emitter->broadcast->emit('other event', 'such data');

?>
```

### Emitting binary
```php
<?php

$emitter = new SocketIO\Emitter(array('port' => '6379', 'host' => '127.0.0.1'));
$binarydata = pack("nvc*", 0x1234, 0x5678, 65, 66);
$emitter->emit('very', new SocketIO\Binary($binarydata));

?>
```
