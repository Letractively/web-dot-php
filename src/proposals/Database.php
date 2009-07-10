<?php
class Database extends MySQLi {

    public function __construct($host, $user, $pass, $db) {

        parent::__construct($host, $user, $pass, $db);

        if (mysqli_connect_error()) {
            $trace = debug_backtrace();
            trigger_error('Database connection "' . mysqli_connect_error() . '" (' . mysqli_connect_errno() . ') in "' . $trace[0]['file'] . '" on line ' . $trace[0]['line'], E_USER_WARNING);
        }
    }
}