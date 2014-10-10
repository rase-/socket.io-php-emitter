<?php

namespace SocketIO;

define('EVENT', 2);
define('BINARY_EVENT', 5);

if (!function_exists('msgpack_pack')) {
  require(__DIR__ . '/msgpack_pack.php');
}

class Emitter {
  public function __construct($redis = FALSE, $opts = array()) {
    if (is_array($redis)) {
      $opts = $redis;
      $redis = FALSE;
    }

    if (!$redis) {
      // Default to phpredis
      if (extension_loaded('redis')) {
        if (!isset($opts['socket']) && !isset($opts['host'])) throw new \Exception('Host should be provided when not providing a redis instance');
        if (!isset($opts['socket']) && !isset($opts['port'])) throw new \Exception('Port should be provided when not providing a redis instance');

        $redis = new \Redis();
        if (isset($opts['socket'])) {
          $redis->connect($opts['socket']);
        } else {
          $redis->connect($opts['host'], $opts['port']);
        }
      } else if(isset($opts['socket']) or isset($opts['host']) or isset($opts['port'])){
        include_once(__DIR__.'/redis_client.php');
        if(isset($opts['socket'])){
          $redis = new Redis('unix://'.$opts['socket']);
        } else {
          $opts = array_merge(
            array('port' => 6379, 'host' => 'localhost'),
            $opts
          );
          $redis = new Redis($opts['host'].':'.$opts['port']);
        }
      } else {
        throw new \Exception('You must provide a valid Redis client or options array.');
      }
    }

    if (!is_callable(array($redis, 'publish'))) {
      throw new \Exception('The Redis client you provided is invalid, Please try another one. For example Credis_Client');
    }

    $this->redis = $redis;
    $this->key = (isset($opts['key']) ? $opts['key'] : 'socket.io') . '#emitter';

    $this->_rooms = array();
    $this->_flags = array();
  }

  /*
   * Flags
   */

  public function __get($flag) {
    $this->_flags[$flag] = TRUE;
    return $this;
  }

  private function readFlag($flag) {
    return isset($this->_flags[$flag]) ? $this->_flags[$flag] : false;
  }

  /*
   * Broadcasting
   */

  public function in($room) {
    if (!in_array($room, $this->_rooms)) {
      $this->_rooms[] = $room;
    }

    return $this;
  }

  // Alias for in
  public function to($room) {
    return in($room);
  }

  /*
   * Namespace
   */

  public function of($nsp) {
    $this->_flags['nsp'] = $nsp;
    return $this;
  }

  /*
   * Emitting
   */

  public function emit() {
    $args = func_get_args();
    $packet = array();

    $packet['type'] = EVENT;
    // handle binary wrapper args
    for ($i = 0; $i < count($args); $i++) {
      $arg = $args[$i];
      if ($arg instanceof Binary) {
        $args[$i] = strval($arg);
        $this->binary;
      }
    }

    if ($this->readFlag('binary')) $packet['type'] = BINARY_EVENT;

    $packet['data'] = $args;

    // set namespace
    if (isset($this->_flags['nsp'])) {
      $packet['nsp'] = $this->_flags['nsp'];
      unset($this->_flags['nsp']);
    } else {
      $packet['nsp'] = '/';
    }

    // publish
    $packed = msgpack_pack(array($packet, array(
      'rooms' => $this->_rooms,
      'flags' => $this->_flags
    )));

    // hack buffer extensions for msgpack with binary
    if ($packet['type'] == BINARY_EVENT) {
      $packed = str_replace(pack('c', 0xda), pack('c', 0xd8), $packed);
      $packed = str_replace(pack('c', 0xdb), pack('c', 0xd9), $packed);
    }

    $this->redis->publish($this->key, $packed);

    // reset state
    $this->_rooms = array();
    $this->_flags = array();

    return $this;
  }
}


