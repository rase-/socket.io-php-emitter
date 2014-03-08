<?php

// Requires msgpack-php (https://github.com/msgpack/msgpack-php) to be
// installed an added as an extension

// Requires phpredis
// (https://github.com/nicolasff/phpredis#installation-on-osx) to be installed
// and added as an extension

define('EVENT', 2);
define('BINARY_EVENT', 5);

class Emitter {
  public function __construct($redis, $opts = array()) {
    if ($redis === NULL) {
      if (!$opts['host']) throw new Error('Host should be provided when not providing a redis instance');
      if (!$opts['port']) throw new Error('Port should be provided when not providing a redis instance');

      $redis = new Redis();
      $redis->connect($opts['host'], $opts['port']);
    }

    $this->redis = $redis;
    $this->key = (isset($opts['key']) ? $opts['key'] : 'socket.io') . '#emitter';

    $this->_rooms = array();
    $this->_flags = array();
  }

  /*
   * Flags
   */

  private function flag($flag) {
    $this->_flags[$flag] = TRUE;
    return $this;
  }

  private function readFlag($flag) {
    return isset($this->_flags[$flag]) ? $this->_flags[$flag] : false;
  }

  public function binary() {
    $this->flag('binary');
  }

  public function json() {
    $this->flag('json');
  }

  public function volatile() {
    $this->flag('volatile');
  }

  public function broadcast() {
    $this->flag('broadcast');
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
   * Emitting
   */

  public function emit() {
    $args = func_get_args();
    $packet = array();

    $packet['type'] = $this->readFlag('binary') ? BINARY_EVENT : EVENT;
    $packet['data'] = $args;

    // publish
    $this->redis->publish($this->key, msgpack_pack([$packet, array(
      'rooms' => $this->_rooms,
      'flags' => $this->_flags
    )]));

    // reset state
    $this->_rooms = array();
    $this->_flags = array();

    return $this;
  }
}
?>
