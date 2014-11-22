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

    $this->assertTrue(stripos($contents, 'publish') !== FALSE);
  }

  public function testDefaultsToLocalHostAndDefaultPort() {
    $p = new Process('redis-cli monitor > redis.log');

    sleep(1);
    // Running this should produce something that's visible in `redis-cli monitor`
    $emitter = new Emitter();
    $emitter->emit('so', 'yo');

    $p->stop();
    $contents= file_get_contents('redis.log');
    unlink('redis.log');

    $this->assertTrue(stripos($contents, 'publish') !== FALSE);
  }


  public function testCanProvideRedisInstance() {
    $p = new Process('redis-cli monitor > redis.log');

    sleep(1);
    // Running this should produce something that's visible in `redis-cli monitor`
    $redis = new \TinyRedisClient('127.0.0.1:6379');
    $emitter = new Emitter($redis);
    $emitter->emit('so', 'yo');

    $p->stop();
    $contents= file_get_contents('redis.log');
    unlink('redis.log');

    $this->assertTrue(stripos($contents, 'publish') !== FALSE);
  }

  public function testPublishContainsExpectedAttributes() {
    $p = new Process('redis-cli monitor > redis.log');

    sleep(1);
    // Running this should produce something that's visible in `redis-cli monitor`
    $emitter = new Emitter(array('host' => '127.0.0.1', 'port' => '6379'));
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
    // Should have the default namespace
    $this->assertTrue(strpos($contents, '/') !== FALSE);
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

  public function testPublishContainsExpectedDataWhenEmittingBinaryWithWrapper() {
    $p = new Process('redis-cli monitor > redis.log');

    sleep(1);
    // Running this should produce something that's visible in `redis-cli monitor`
    $emitter = new Emitter(NULL, array('host' => '127.0.0.1', 'port' => '6379'));
    $binarydata = pack('CCCCC', 0, 1, 2, 3, 4);
    $emitter->emit('binary event', new SocketIO\Binary($binarydata));

    $p->stop();
    $contents= file_get_contents('redis.log');
    unlink('redis.log');

    $this->assertTrue(strpos($contents, '\x00\x01\x02\x03\x04') !== FALSE);
  }

  public function testPublishContainsNamespaceWhenEmittingWithNamespaceSet() {
    $p = new Process('redis-cli monitor > redis.log');

    sleep(1);
    // Running this should produce something that's visible in `redis-cli monitor`
    $emitter = new Emitter(NULL, array('host' => '127.0.0.1', 'port' => '6379'));
    $emitter->of('/nsp')->emit('yolo', 'data');

    $p->stop();
    $contents= file_get_contents('redis.log');
    unlink('redis.log');

    $this->assertTrue(strpos($contents, '/nsp') !== FALSE);
  }
}
?>
