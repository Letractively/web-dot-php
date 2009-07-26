<?php
class Redis {

    private function __construct() {}

    public static function connect($host, $port) {
        $self->socket = fsockopen($host, $port);
    }

    public static function disconnect($host, $port) {
        
    }


    public function quit() {
        write("QUIT\r\n");
    }

    public function ping() {
        write("PING\r\n");
        return fgets($fp);
    }

    private function write($command) {
        do {
            $i = fwrite($fp, $command);
            if ($i == 0) break;
            $command = substr($command, $i);
        } while ($command);
    }
}

echo 'PING: ', ping(), '<br />';
echo 'QUIT: ', quit(), '<br />';

fclose($fp);

