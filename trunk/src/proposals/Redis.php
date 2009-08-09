<?php
class Redis {

    function __construct($host = 'tcp://127.0.0.1', $port = '6379') {
        $this->host = $host;
        $this->port = $port;
    }

    function _echo($data) {
        $this->write(sprintf("ECHO %u\r\n%s\r\n", strlen($data), $data));
        return $this->read();
    }

    function ping() {
        $this->write("PING\r\n");
        return $this->read();
    }

    function auth($password) {
        $this->write(sprintf("AUTH %s\r\n", $password));
        return $this->read();
    }

    function type($key) {
        $this->write(sprintf("TYPE %s\r\n", $key));
        return $this->read();
    }

    function set($key, $value) {
        $this->write(sprintf("SET %s %u\r\n%s\r\n", $key, strlen($value), $value));
        return (bool) $this->read();
    }

    function setnx($key, $value) {
        $this->write(sprintf("SETNX %s %u\r\n%s\r\n", $key, strlen($value), $value));
        return (bool) $this->read();
    }

    function get($key) {
        $this->write(sprintf("GET %s\r\n", $key));
        return $this->read();
    }

    function getset($key, $value) {
        $this->write(sprintf("GETSET %s %u\r\n%s\r\n", $key, strlen($value), $value));
        return $this->read();
    }

    function del($key) {
        $this->write(sprintf("DEL %s\r\n", $key));
        return (bool) $this->read();
    }

    function incr($key) {
        $this->write(sprintf("INCR %s\r\n", $key));
        return $this->read();
    }

    function incrby($key, $increment = 1) {
        $this->write(sprintf("INCRBY %s %d\r\n", $key, $increment));
        return $this->read();
    }

    function decr($key) {
        $this->write(sprintf("DECR %s\r\n", $key));
        return $this->read();
    }

    function decrby($key, $increment = 1) {
        $this->write(sprintf("DECRBY %s %d\r\n", $key, $increment));
        return $this->read();
    }

    function exists($key) {
        $this->write(sprintf("EXISTS %s\r\n", $key));
        return (bool) $this->read();
    }

    function llen($key) {
        $this->write(sprintf("LLEN %s\r\n", $key));
        return $this->read();
    }

    function lpush($key, $value) {
        $this->write(sprintf("LPUSH %s %u\r\n%s\r\n", $key, strlen($value), $value));
        return $this->read();
    }

    function lrange($key, $start = 0, $end = -1) {
        $this->write(sprintf("LRANGE %s %u %d\r\n", $key, $start, $end));
        return $this->read();
    }

    function quit() {
        $this->write("QUIT\r\n");
        fclose($this->socket);
        unset($this->socket);
    }

    function shutdown() {
        $this->write("SHUTDOWN\r\n");
        fclose($this->socket);
        unset($this->socket);
    }

    private function write($command) {
        if (!isset($this->socket)) $this->socket = fsockopen($this->host, $this->port);
        do {
            $i = fwrite($this->socket, $command);
            if ($i == 0) break;
            $command = substr($command, $i);
        } while ($command);
    }

    private function read() {

        $type = fgetc($this->socket);
        $data = fgets($this->socket);

        switch ($type) {
            case '-':
                return trigger_error(substr($data, 4), E_USER_WARNING);
            case '+':
                return substr($data, 0, strlen($data) - 2);
            case '$':
                $size = (int) $data;
                switch ($size) {
                    case -1: $data = null; break;
                    case  0: $data = ''; break;
                    default: $data = fread($this->socket, $size);
                }
                fread($this->socket, 2);
                return $data;
            case '*':
                $size = (int) $data;
                switch ($size) {
                    case -1: return null;
                    case  0: return array();
                    default:
                        $data = array();
                        do { $data[] = $this->read(); } while (--$size > 0);
                        return $data;
                }
            case ':':
                return (int) $data;
        }
    }
}

$starttime = microtime(true);

$r = new Redis;
echo 'AUTH wrongpass: ', var_dump($r->auth('wrongpass')), '<br />', PHP_EOL;
echo 'AUTH foobared: ', var_dump($r->auth('foobared')), '<br />', PHP_EOL;
echo 'PING: ', var_dump($r->ping()), '<br />', PHP_EOL;
echo 'QUIT: ', var_dump($r->quit()), '<br />', PHP_EOL;
echo 'AUTH foobared: ', var_dump($r->auth('foobared')), '<br />', PHP_EOL;
echo 'PING: ', var_dump($r->ping()), '<br />', PHP_EOL;
echo 'ECHO test: ', var_dump($r->_echo('test')), '<br />', PHP_EOL;
echo 'ECHO: ', var_dump($r->_echo('')), '<br />', PHP_EOL;
echo 'DEL key: ', var_dump($r->del('key')), '<br />', PHP_EOL;
echo 'EXISTS key: ', var_dump($r->exists('key')), '<br />', PHP_EOL;
echo 'SET key 123: ', var_dump($r->set('key', '123')), '<br />', PHP_EOL;
echo 'GET key: ', var_dump($r->get('key')), '<br />', PHP_EOL;
echo 'GETSET key 456: ', var_dump($r->getset('key', '456')), '<br />', PHP_EOL;
echo 'GET key: ', var_dump($r->get('key')), '<br />', PHP_EOL;
echo 'EXISTS key: ', var_dump($r->exists('key')), '<br />', PHP_EOL;
echo 'SETNX key abc: ', var_dump($r->setnx('key', 'abc')), '<br />', PHP_EOL;
echo 'GET key: ', var_dump($r->get('key')), '<br />', PHP_EOL;
echo 'DEL key: ', var_dump($r->del('key')), '<br />', PHP_EOL;
echo 'SETNX key abc: ', var_dump($r->setnx('key', 'abc')), '<br />', PHP_EOL;
echo 'GET key: ', var_dump($r->get('key')), '<br />', PHP_EOL;
echo 'TYPE key: ', var_dump($r->type('key')), '<br />', PHP_EOL;
echo 'LLEN key: ', var_dump($r->llen('key')), '<br />', PHP_EOL;
echo 'DEL key: ', var_dump($r->del('key')), '<br />', PHP_EOL;
echo 'INCR key: ', var_dump($r->incr('key')), '<br />', PHP_EOL;
echo 'INCR key: ', var_dump($r->incr('key')), '<br />', PHP_EOL;
echo 'TYPE key: ', var_dump($r->type('key')), '<br />', PHP_EOL;
echo 'DEL key: ', var_dump($r->del('key')), '<br />', PHP_EOL;
echo 'LPUSH key: ', var_dump($r->lpush('key', '123')), '<br />', PHP_EOL;
echo 'TYPE key: ', var_dump($r->type('key')), '<br />', PHP_EOL;
echo 'LLEN key: ', var_dump($r->llen('key')), '<br />', PHP_EOL;
echo 'LPUSH key 456: ', var_dump($r->lpush('key', '456')), '<br />', PHP_EOL;
echo 'LLEN key: ', var_dump($r->llen('key')), '<br />', PHP_EOL;
echo 'LRANGE key: ', var_dump($r->lrange('key')), '<br />', PHP_EOL;


$r->quit();

//echo 'SHUTDOWN: ', $r->shutdown(), '<br />', PHP_EOL;


echo microtime(true) - $starttime;

