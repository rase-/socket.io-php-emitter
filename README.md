socket.io-php-emitter
=====================

A PHP implementation of socket.io-emitter.

This project uses [msgpack-php](https://github.com/msgpack/msgpack-php) and [phpredis](https://github.com/nicolasff/phpredis). Make sure to have those extensions in use before trying to use the emitter.

## Usage

```php
<?php
include 'emitter.php';

$emitter = new Emitter(NULL, array('port' => '6379', 'host' => '127.0.0.1'));
$emitter->emit('yo', 'so');


$redis = new Redis();
$redis->connect('127.0.0.1', '6379');
$emitter = new Emitter($redis);
$emitter->emit('xiit', 'woot');
?>
```
