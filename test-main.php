<?php
include 'emitter.php';

$emitter = new Emitter(NULL, array('port' => '6379', 'host' => '127.0.0.1'));
$emitter->emit('yo', 'so');


$redis = new Redis();
$redis->connect('127.0.0.1', '6379');
$emitter = new Emitter($redis);
$emitter->emit('xiit', 'woot');
?>
