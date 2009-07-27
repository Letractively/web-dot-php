<?php
class Redis {

    function __construct($host = 'tcp://127.0.0.1', $port = '6379') {
        $this->host = $host;
        $this->port = $port;
    }

    function del($key) {
        $this->write(sprintf("DEL %s\r\n", $key));
        return (bool) $this->read();
    }

    function _echo($data) {
        $this->write(sprintf("ECHO %u\r\n%s\r\n", strlen($data), $data));
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

    function ping() {
        $this->write("PING\r\n");
        return $this->read();
    }

    function quit() {
        $this->write("QUIT\r\n");
        fclose($this->socket);
        unset($this->socket);
    }

    function set($key, $value) {
        $this->write(sprintf("SET %s %u\r\n%s\r\n", $key, strlen($value), $value));
        return $this->read();
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

        switch ($type) {
            case '-':
                $data = fgets($this->socket);
                return trigger_error(substr($data, 0, strlen($data) - 2), E_USER_ERROR);
            case '+':
                $data = fgets($this->socket);
                return substr($data, 0, strlen($data) - 2);
            case '$':
                $size = (int) fgets($this->socket);
                switch ($size) {
                    case -1: $data = null; break;
                    case  0: $data = ''; break;
                    default: $data = fread($this->socket, $size);
                }
                fread($this->socket, 2);
                return $data;
            case '*':
                $data = array();
                $size = (int) fgets($this->socket);
                switch ($size) {
                    case -1: $data = null; break;
                    case  0: break;
                    default:
                        do { $data[] = $this->read(); } while (--$size > 0);
                }
                return $data;
            case ':':
                return (int) fgets($this->socket);
        }
    }
}

$r = new Redis;

echo 'QUIT: ', var_dump($r->quit()), '<br />', PHP_EOL;
echo 'PING: ', var_dump($r->ping()), '<br />', PHP_EOL;
echo 'ECHO test: ', var_dump($r->_echo('test')), '<br />', PHP_EOL;
echo 'ECHO: ', var_dump($r->_echo('')), '<br />', PHP_EOL;
echo 'DEL key: ', var_dump($r->del('key')), '<br />', PHP_EOL;
echo 'EXISTS key: ', var_dump($r->exists('key')), '<br />', PHP_EOL;
echo 'SET key 123: ', var_dump($r->set('key', '123')), '<br />', PHP_EOL;
echo 'EXISTS key: ', var_dump($r->exists('key')), '<br />', PHP_EOL;
echo 'LLEN key: ', var_dump($r->llen('key')), '<br />', PHP_EOL;


$r->quit();

//echo 'SHUTDOWN: ', $r->shutdown(), '<br />', PHP_EOL;


