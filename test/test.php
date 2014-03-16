<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
include 'process.php';

use SocketIO\Emitter;

class EmitterTest extends PHPUnit_Framework_TestCase {
  public function testEmitCreatesARedisPublish() {
    $p = new Process('redis-cli monitor > redis.log');

    sleep(1);
    // Running this should produce something that's visible in `redis-cli monitor`
    $emitter = new Emitter(NULL, array('host' => '127.0.0.1', 'port' => '6379'));
    $emitter->emit('so', 'yo');

    $p->stop();
    $contents= file_get_contents('redis.log');
    unlink('redis.log');

    $this->assertTrue(strpos($contents, 'PUBLISH') !== FALSE);
  }

  public function testPublishContainsExpectedAttributes() {
    $p = new Process('redis-cli monitor > redis.log');

    sleep(1);
    // Running this should produce something that's visible in `redis-cli monitor`
    $emitter = new Emitter(NULL, array('host' => '127.0.0.1', 'port' => '6379'));
    $emitter->emit('so', 'yo');

    $p->stop();
    $contents= file_get_contents('redis.log');
    unlink('redis.log');

    $this->assertTrue(strpos($contents, 'so') !== FALSE);
    $this->assertTrue(strpos($contents, 'yo') !== FALSE);
    $this->assertTrue(strpos($contents, 'rooms') !== FALSE);
    $this->assertTrue(strpos($contents, 'flags') !== FALSE);
    // Should not broadcast by default
    $this->assertFalse(strpos($contents, 'broadcast') !== FALSE);
  }

  public function testPublishContainsBroadcastWhenBroadcasting() {
    $p = new Process('redis-cli monitor > redis.log');

    sleep(1);
    // Running this should produce something that's visible in `redis-cli monitor`
    $emitter = new Emitter(NULL, array('host' => '127.0.0.1', 'port' => '6379'));
    $emitter->broadcast->emit('so', 'yo');

    $p->stop();
    $contents= file_get_contents('redis.log');
    unlink('redis.log');

    $this->assertTrue(strpos($contents, 'so') !== FALSE);
    $this->assertTrue(strpos($contents, 'yo') !== FALSE);
    $this->assertTrue(strpos($contents, 'rooms') !== FALSE);
    $this->assertTrue(strpos($contents, 'flags') !== FALSE);
    $this->assertTrue(strpos($contents, 'broadcast') !== FALSE);
  }

  public function testPublishContainsExpectedDataWhenEmittingBinary() {
    $p = new Process('redis-cli monitor > redis.log');

    sleep(1);
    // Running this should produce something that's visible in `redis-cli monitor`
    $emitter = new Emitter(NULL, array('host' => '127.0.0.1', 'port' => '6379'));
    $emitter->binary;
    $binarydata = pack('CCCCC', 0, 1, 2, 3, 4);
    $emitter->emit('binary event', $binarydata);

    $p->stop();
    $contents= file_get_contents('redis.log');
    unlink('redis.log');

    $this->assertTrue(strpos($contents, '\x00\x01\x02\x03\x04') !== FALSE);
  }
}
?>
