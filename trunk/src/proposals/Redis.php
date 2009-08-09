<?php
class Redis {

    function __construct($host = 'tcp://127.0.0.1', $port = '6379') {
        $this->host = $host;
        $this->port = $port;
    }

    function _echo($data) { return $this->write(sprintf("ECHO %u\r\n%s\r\n", strlen($data), $data)); }
    function ping() { return $this->write("PING\r\n"); }
    function auth($password) { return $this->write(sprintf("AUTH %s\r\n", $password)); }
    function type($key) { return $this->write(sprintf("TYPE %s\r\n", $key)); }
    function set($key, $value) { return (bool) $this->write(sprintf("SET %s %u\r\n%s\r\n", $key, strlen($value), $value)); }
    function setnx($key, $value) { return (bool) $this->write(sprintf("SETNX %s %u\r\n%s\r\n", $key, strlen($value), $value)); }
    function get($key) { return $this->write(sprintf("GET %s\r\n", $key)); }
    function getset($key, $value) { return $this->write(sprintf("GETSET %s %u\r\n%s\r\n", $key, strlen($value), $value)); }
    function del($key) { return (bool) $this->write(sprintf("DEL %s\r\n", $key)); }
    function incr($key) { return $this->write(sprintf("INCR %s\r\n", $key)); }
    function incrby($key, $increment = 1) { return $this->write(sprintf("INCRBY %s %d\r\n", $key, $increment)); }
    function decr($key) { return $this->write(sprintf("DECR %s\r\n", $key)); }
    function decrby($key, $increment = 1) { return $this->write(sprintf("DECRBY %s %d\r\n", $key, $increment)); }
    function exists($key) { return (bool) $this->write(sprintf("EXISTS %s\r\n", $key)); }
    function llen($key) { return $this->write(sprintf("LLEN %s\r\n", $key)); }
    function lpush($key, $value) { return $this->write(sprintf("LPUSH %s %u\r\n%s\r\n", $key, strlen($value), $value)); }
    function lrange($key, $start = 0, $end = -1) { return $this->write(sprintf("LRANGE %s %u %d\r\n", $key, $start, $end)); }
    function quit() { return $this->write("QUIT\r\n", true); }
    function shutdown() { return $this->write("SHUTDOWN\r\n", true); }

    private function write($command, $disconnect = false) {
        if (!isset($this->socket)) $this->socket = fsockopen($this->host, $this->port);
        do {
            $i = fwrite($this->socket, $command);
            if ($i == 0) return;
            $command = substr($command, $i);
        } while ($command);

        if ($disconnect) {
            fclose($this->socket);
            unset($this->socket);
            return;
        }

        return $this->read();
    }

    private function read() {

        $type = fgetc($this->socket);
        $data = fgets($this->socket);

        switch ($type) {
            case '-': return trigger_error(substr($data, 4), E_USER_WARNING);
            case '+': return substr($data, 0, -2);
            case ':': return (int) $data;
            case '$':
                $size = (int) $data;
                if ($size == -1) return null;
                if ($size == 0) return '';
                $data = fread($this->socket, $size);
                fread($this->socket, 2);
                return $data;
            case '*':
                $size = (int) $data;
                $data = $size == -1 ? null : array();
                while (--$size > -1) $data[] = $this->read();
                return $data;
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

