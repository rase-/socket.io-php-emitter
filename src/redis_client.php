<?php
// from https://github.com/ptrofimov/tinyredisclient
namespace SocketIO;
class Redis{
    /** @var resource */
    private $socket;

    public function __construct($server = 'localhost:6379'){
    	$this->socket = stream_socket_client($server);
    }

    public function __call($method, array $args){
        array_unshift($args, $method);
        $cmd = '*' . count($args) . "\r\n";
        foreach ($args as $item) {
            $cmd .= '$' . strlen($item) . "\r\n" . $item . "\r\n";
        }
        fwrite($this->socket, $cmd);

        return $this->parseResponse();
    }

    private function parseResponse(){
        $line = fgets($this->socket);
        list($type, $result) = array($line[0], substr($line, 1, strlen($line) - 3));
        if ($type == '-') { // error message
            throw new Exception($result);
        } elseif ($type == '$') { // bulk reply
            if ($result == -1) {
                $result = null;
            } else {
                $line = fread($this->socket, $result + 2);
                $result = substr($line, 0, strlen($line) - 2);
            }
        } elseif ($type == '*') { // multi-bulk reply
            $count = ( int ) $result;
            for ($i = 0, $result = array(); $i < $count; $i++) {
                $result[] = $this->parseResponse();
            }
        }

        return $result;
    }
}