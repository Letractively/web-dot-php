<?php
require 'db.news.php';
require 'db.chat.php';
require 'db.bets.php';
require 'db.users.php';
require 'db.teams.php';
require 'db.install.php';

class db extends SQLite3 {
    public $news, $bets, $chat, $users, $teams, $install;
    function __construct() {
        parent::__construct(realpath(__DIR__ . '/../data/phpbetz.sq3'));
        $this->news = new news($this);
        $this->bets = new bets($this);
        $this->chat = new chat($this);
        $this->users = new users($this);
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