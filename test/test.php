<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

use SocketIO\Emitter;

// Running this should produce something that's visible in `redis-cli monitor`
$emitter = new Emitter(NULL, array('host' => '127.0.0.1', 'port' => '6379'));
$emitter->emit('so', 'yo');
?>
