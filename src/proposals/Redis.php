<?php
class Redis {

    function __construct($host = 'tcp://127.0.0.1', $port = '6379') {
        $this->host = $host;
        $this->port = $port;
    }

    // Connection handling
    function quit() { return $this->write("QUIT\r\n", true); }
    function auth($password) { return $this->write(sprintf("AUTH %s\r\n", $password)); }

    // Connection testing
    function ping() { return $this->write("PING\r\n"); }
    function echoes($data) { return $this->write(sprintf("ECHO %u\r\n%s\r\n", strlen($data), $data)); }

    // Commands operating on string values
    function set($key, $value) { return $this->write(sprintf("SET %s %u\r\n%s\r\n", $key, strlen($value), $value)); }
    function get($key) { return $this->write(sprintf("GET %s\r\n", $key)); }
    function getset($key, $value) { return $this->write(sprintf("GETSET %s %u\r\n%s\r\n", $key, strlen($value), $value)); }
    function mget() { return $this->write(sprintf("MGET %s\r\n", implode(' ', func_get_args()))); }
    function setnx($key, $value) { return $this->write(sprintf("SETNX %s %u\r\n%s\r\n", $key, strlen($value), $value)); }
    function incr($key) { return $this->write(sprintf("INCR %s\r\n", $key)); }
    function incrby($key, $increment = 1) { return $this->write(sprintf("INCRBY %s %d\r\n", $key, $increment)); }
    function decr($key) { return $this->write(sprintf("DECR %s\r\n", $key)); }
    function decrby($key, $decrement = 1) { return $this->write(sprintf("DECRBY %s %d\r\n", $key, $decrement)); }
    function exists($key) { return $this->write(sprintf("EXISTS %s\r\n", $key)); }
    function del() { return $this->write(sprintf("DEL %s\r\n", implode(' ', func_get_args()))); }
    function type($key) { return $this->write(sprintf("TYPE %s\r\n", $key)); }

    // Commands operating on the key space
    function keys($pattern) { return $this->write(sprintf("KEYS %s\r\n", $pattern)); }
    function randomkey() { return $this->write("RANDOMKEY\r\n"); }
    function rename($oldkey, $newkey) { return $this->write(sprintf("RENAME %s %s\r\n", $oldkey, $newkey)); }
    function renamenx($oldkey, $newkey) { return $this->write(sprintf("RENAMENX %s %s\r\n", $oldkey, $newkey)); }
    function dbsize() { return $this->write("DBSIZE\r\n"); }
    function expire($key, $timeout = 0) { return $this->write(sprintf("EXPIRE %s %u\r\n", $key, $timeout)); }
    function ttl($key) { return $this->write(sprintf("TTL %s\r\n", $key)); }

    // Commands operating on lists
    function rpush($key, $value) { return $this->write(sprintf("RPUSH %s %u\r\n%s\r\n", $key, strlen($value), $value)); }
    function lpush($key, $value) { return $this->write(sprintf("LPUSH %s %u\r\n%s\r\n", $key, strlen($value), $value)); }
    function llen($key) { return $this->write(sprintf("LLEN %s\r\n", $key)); }
    function lrange($key, $start = 0, $end = -1) { return $this->write(sprintf("LRANGE %s %d %d\r\n", $key, $start, $end)); }
    function ltrim($key, $start = 0, $end = -1) { return $this->write(sprintf("LTRIM %s %d %d\r\n", $key, $start, $end)); }
    function lindex($key, $index = 0) { return $this->write(sprintf("LINDEX %s %d\r\n", $key, $index)); }
    function lset($key, $index, $value) { return $this->write(sprintf("LSET %s %d %u\r\n%s\r\n", $key, $index, strlen($value), $value)); }
    function lrem($key, $count = 0, $value = null) { return $this->write(sprintf('LREN %s %d', $key, $count) . isset($value) ? sprintf(" %u\r\n%s\r\n", strlen($value), $value) : "\r\n"); }
    function lpop($key) { return $this->write(sprintf("LPOP %s\r\n", $key)); }
    function rpop($key) { return $this->write(sprintf("RPOP %s\r\n", $key)); }

    // Commands operating on sets
    function sadd($key, $member) { return $this->write(sprintf("SADD %s %u\r\n%s\r\n", $key, strlen($member), $member)); }
    function srem($key, $member) { return $this->write(sprintf("SREM %s %u\r\n%s\r\n", $key, strlen($member), $member)); }
    function spop($key) { return $this->write(sprintf("SPOP %s\r\n", $key)); }
    function smove($srkey, $dstkey, $member) { return $this->write(sprintf("SMOVE %s %s %u\r\n%s\r\n", $srckey, $dstkey, $strlen($member), $member)); }
    function scard($key) { return $this->write(sprintf("SCARD %s\r\n", $key)); }
    function sismember($key, $member) { return $this->write(sprintf("SISMEMBER %s %u\r\n%s\r\n", $key, strlen($member), $member)); }
    function sinter() { return $this->write(sprintf("SINTER %s\r\n", implode(' ', func_get_args()))); }
    function sinterstore() { return $this->write(sprintf("SINTERSTORE %s\r\n", implode(' ', func_get_args()))); }
    function sunion() { return $this->write(sprintf("SUNION %s\r\n", implode(' ', func_get_args()))); }
    function sunionstore() { return $this->write(sprintf("SUNIONSTORE %s\r\n", implode(' ', func_get_args()))); }
    function sdiff() { return $this->write(sprintf("SDIFF %s\r\n", implode(' ', func_get_args()))); }
    function sdiffstore() { return $this->write(sprintf("SDIFFSTORE %s\r\n", implode(' ', func_get_args()))); }
    function smembers($key) { return $this->write(sprintf("SMEMBERS %s\r\n", $key)); }

    // Multiple databases handling commands
    function select($dbindex = 0) { return $this->write(sprintf("SELECT %u\r\n", $dbindex)); }
    function move($key, $dbindex) { return $this->write(sprintf("MOVE %s %u\r\n", $key, $dbindex)); }
    function flushdb() { return $this->write("FLUSHDB\r\n"); }
    function flushall() { return $this->write("FLUSHALL\r\n"); }

    // Sorting
    function sort($key) { return new RedisSortCommand($this, $key); }

    // Persistence control commands
    function save() { return $this->write("SAVE\r\n"); }
    function bgsave() { return $this->write("BGSAVE\r\n"); }
    function lastsave() { return $this->write("LASTSAVE\r\n"); }
    function shutdown() { return $this->write("SHUTDOWN\r\n", true); }

    // Remote server control commands *
    function info() { return $this->write("INFO\r\n"); }

    private function write($command, $disconnect = false) {
        if (!isset($this->socket)) $this->socket = fsockopen($this->host, $this->port);
        fwrite($this->socket, $command);
        return $disconnect ? $this->disconnect() : $this->read();
    }

    private function read() {

        // Alternative method (one less round trips):
        //$data = fgets($this->socket);
        //$type = substr($data, 0, 1);
        //$data = substr($data, 1, -2);

        $type = fgetc($this->socket);
        $data = substr(fgets($this->socket), 0, -2);

        switch ($type) {
            case '-': return trigger_error(substr($data, 4), E_USER_WARNING);
            case '+': return $data;
            case ':': return (int) $data;
            case '$':
                $size = (int) $data;
                if ($size > 0) $data = fread($this->socket, $size);
                fread($this->socket, 2);
                if ($size == -1) return null;
                if ($size == 0) return '';
                return $data;
            case '*':
                $size = (int) $data;
                $data = $size == -1 ? null : array();
                while (--$size > -1) $data[] = $this->read();
                return $data;
        }
    }

    private function disconnect() {
        fclose($this->socket);
        unset($this->socket);
    }
}

class RedisSortCommand {

    function __construct($redis, $key) {
        $this->redis = $redis;
        $this->key = $key;
    }

    function by($pattern) {
        $this->by = $pattern;
        return $this;
    }

    function limit($start, $end) {
        $this->start = $start;
        $this->end = $end;
        return $this;
    }

    function get($pattern) {
        $this->get = $pattern;
        return $this;
    }

    function alpha() {
        $this->alpha = true;
        return $this;
    }

    function asc() {
        $this->order = 'ASC';
        return $this;
    }

    function desc() {
        $this->order = 'DESC';
        return $this;
    }

    function query() {
        return $this->redis->write(strval($this));
    }

    function __toString() {
        $cmd = 'SORT ' . $key;
        if (isset($this->by)) $cmd .= ' BY ' . $this->by;
        if (isset($this->start) && isset($this->end)) $cmd .= ' LIMIT ' . $this->start . ' ' . $this->end;
        if (isset($this->get)) $cmd .= ' GET ' . $this->get;
        if (isset($this->alpha)) $cmd .= ' ALPHA';
        if (isset($this->order)) $cmd .= ' ' . $this->order;
    }
}
/*
$starttime = microtime(true);

$r = new Redis;

echo 'AUTH wrongpass: ', var_dump($r->auth('wrongpass')), '<br />', PHP_EOL;
echo 'AUTH foobared: ', var_dump($r->auth('foobared')), '<br />', PHP_EOL;
echo 'PING: ', var_dump($r->ping()), '<br />', PHP_EOL;
echo 'QUIT: ', var_dump($r->quit()), '<br />', PHP_EOL;
echo 'AUTH foobared: ', var_dump($r->auth('foobared')), '<br />', PHP_EOL;
echo 'PING: ', var_dump($r->ping()), '<br />', PHP_EOL;
echo 'ECHO test: ', var_dump($r->echoes('test')), '<br />', PHP_EOL;
echo 'ECHO: ', var_dump($r->echoes('')), '<br />', PHP_EOL;
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
echo 'SET a 123: ', var_dump($r->set('a', '123')), '<br />', PHP_EOL;
echo 'SET b 456: ', var_dump($r->set('b', '456')), '<br />', PHP_EOL;
echo 'SET c 789: ', var_dump($r->set('c', '789')), '<br />', PHP_EOL;
echo 'SET key: ', var_dump($r->set('key', '')), '<br />', PHP_EOL;
echo 'MGET a b c key: ', var_dump($r->mget('a', 'b', 'c', 'key')), '<br />', PHP_EOL;
echo 'DEL a b c key: ', var_dump($r->del('a', 'b', 'c', 'key')), '<br />', PHP_EOL;
echo 'EXISTS key: ', var_dump($r->exists('key')), '<br />', PHP_EOL;
echo 'LASTSAVE: ', var_dump($r->lastsave()), '<br />', PHP_EOL;
echo 'SAVE: ', var_dump($r->save()), '<br />', PHP_EOL;
echo 'LASTSAVE: ', var_dump($r->lastsave()), '<br />', PHP_EOL;
echo 'BGSAVE: ', var_dump($r->bgsave()), '<br />', PHP_EOL;
echo 'LASTSAVE: ', var_dump($r->lastsave()), '<br />', PHP_EOL;
echo 'FLUSHDB: ', var_dump($r->flushdb()), '<br />', PHP_EOL;
echo 'FLUSHALL: ', var_dump($r->flushall()), '<br />', PHP_EOL;
$r->quit();

//echo 'SHUTDOWN: ', $r->shutdown(), '<br />', PHP_EOL;

echo microtime(true) - $starttime;
*/