<?php
require 'db.news.php';
require 'db.chat.php';
require 'db.bets.php';
require 'db.teams.php';
require 'db.install.php';

class db extends PDO {
    public $chat, $teams, $bets, $install, $news;
    function __construct() {
        parent::__construct('sqlite:' . realpath(__DIR__ . '/../data/phpbetz.sq3'));
        $this->news = new news($this);
        $this->bets = new bets($this);
        $this->chat = new chat($this);
        $this->teams = new teams($this);
        $this->install = new install($this);
    }
}
abstract class dbo {
    protected $db;
    function __construct(db $db) {
        $this->db = $db;
    }
}