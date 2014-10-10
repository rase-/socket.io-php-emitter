<?php

namespace SocketIO;

define('EVENT', 2);
define('BINARY_EVENT', 5);

class Emitter {
     public function __construct($redis = FALSE, $opts = array()){
          if(!$redis)
               throw new /Exception("You must provide a valid Redis Client. It is needed to talk to socket.io");
          if(!is_callable($redis,'publish'))
               throw new /Exception("The Redis client you provided is invalid, Please try another one. For example Credis_Client");
          $this->redis = $redis;
          $this->key = (isset($opts['key']) ? $opts['key'] : 'socket.io') . '#emitter';

          $this->_rooms = array();
          $this->_flags = array();
     }

     /*
      * Flags
      */

     public function __get($flag){
          $this->_flags[$flag] = TRUE;
          return $this;
     }

     private function readFlag($flag){
          return isset($this->_flags[$flag]) ? $this->_flags[$flag] : false;
     }

     /*
      * Broadcasting
      */

     public function in($room){
          if (!in_array($room, $this->_rooms)) {
               $this->_rooms[] = $room;
          }

          return $this;
     }

     // Alias for in
     public function to($room){
          return in($room);
     }

     /*
      * Namespace
      */

     public function of($nsp){
          $this->_flags['nsp'] = $nsp;
          return $this;
     }

     /*
      * Emitting
      */

     public function emit(){
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
          $packed = msgpack_pack([$packet, array(
               'rooms' => $this->_rooms,
               'flags' => $this->_flags
          )]);

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

// From https://github.com/onlinecity/msgpack-php
if (!function_exists('msgpack_pack')) {
     function msgpack_pack($input){
          static $bigendian;
          if (!isset($bigendian)) $bigendian = (pack('S', 1) == pack('n', 1));

          // null
          if (is_null($input)) {
               return pack('C', 0xC0);
          }

          // booleans
          if (is_bool($input)) {
               return pack('C', $input ? 0xC3 : 0xC2);
          }

          // Integers
          if (is_int($input)) {
               // positive fixnum
               if (($input | 0x7F) == 0x7F) return pack('C', $input & 0x7F);
               // negative fixnum
               if ($input < 0 && $input >= -32) return pack('c', $input);
               // uint8
               if ($input > 0 && $input <= 0xFF) return pack('CC', 0xCC, $input);
               // uint16
               if ($input > 0 && $input <= 0xFFFF) return pack('Cn', 0xCD, $input);
               // uint32
               if ($input > 0 && $input <= 0xFFFFFFFF) return pack('CN', 0xCE, $input);
               // uint64
               if ($input > 0 && $input <= 0xFFFFFFFFFFFFFFFF) {
                    // pack() does not support 64-bit ints, so pack into two 32-bits
                    $h = ($input & 0xFFFFFFFF00000000) >> 32;
                    $l = $input & 0xFFFFFFFF;
                    return $bigendian ? pack('CNN', 0xCF, $l, $h) : pack('CNN', 0xCF, $h, $l);
               }
               // int8
               if ($input < 0 && $input >= -0x80) return pack('Cc', 0xD0, $input);
               // int16
               if ($input < 0 && $input >= -0x8000) {
                    $p = pack('s', $input);
                    return pack('Ca2', 0xD1, $bigendian ? $p : strrev($p));
               }
               // int32
               if ($input < 0 && $input >= -0x80000000) {
                    $p = pack('l', $input);
                    return pack('Ca4', 0xD2, $bigendian ? $p : strrev($p));
               }
               // int64
               if ($input < 0 && $input >= -0x8000000000000000) {
                    // pack() does not support 64-bit ints either so pack into two 32-bits
                    $p1 = pack('l', $input & 0xFFFFFFFF);
                    $p2 = pack('l', ($input >> 32) & 0xFFFFFFFF);
                    return $bigendian ? pack('Ca4a4', 0xD3, $p1, $p2) : pack('Ca4a4', 0xD3, strrev($p2), strrev($p1));
               }
               throw new \InvalidArgumentException('Invalid integer: ' . $input);
          }

          // Floats
          if (is_float($input)) {
               // Just pack into a double, don't take any chances with single precision
               return pack('C', 0xCB) . ($bigendian ? pack('d', $input) : strrev(pack('d', $input)));
          }

          // Strings/Raw
          if (is_string($input)) {
               $len = strlen($input);
               if ($len < 32) {
                    return pack('Ca*', 0xA0 | $len, $input);
               } else if ($len <= 0xFFFF) {
                    return pack('Cna*', 0xDA, $len, $input);
               } else if ($len <= 0xFFFFFFFF) {
                    return pack('CNa*', 0xDB, $len, $input);
               } else {
                    throw new \InvalidArgumentException('Input overflows (2^32)-1 byte max');
               }
          }

          // Arrays & Maps
          if (is_array($input)) {
               $keys = array_keys($input);
               $len = count($input);

               // Is this an associative array?
               $isMap = false;
               foreach ($keys as $key) {
                    if (!is_int($key)) {
                         $isMap = true;
                         break;
                    }
               }

               $buf = '';
               if ($len < 16) {
                    $buf .= pack('C', ($isMap ? 0x80 : 0x90) | $len);
               } else if ($len <= 0xFFFF) {
                    $buf .= pack('Cn', ($isMap ? 0xDE : 0xDC), $len);
               } else if ($len <= 0xFFFFFFFF) {
                    $buf .= pack('CN', ($isMap ? 0xDF : 0xDD), $len);
               } else {
                    throw new \InvalidArgumentException('Input overflows (2^32)-1 max elements');
               }

               foreach ($input as $key => $elm) {
                    if ($isMap) $buf .= msgpack_pack($key);
                    $buf .= msgpack_pack($elm);
               }
               return $buf;

          }

          throw new \InvalidArgumentException('Not able to pack/serialize input type: ' . gettype($input));
     }
}