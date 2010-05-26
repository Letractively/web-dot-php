<?php
class db extends PDO {
    public $pdo, $chat, $install;
    function __construct() {
        $this->pdo = new PDO(sprintf('sqlite:%s/db/phpbetz.sq3', __DIR__));
        $this->chat = new chat($this->pdo);
        $this->install = new install($this->pdo);
    }
}
abstract class dbo {
    function __construct($pdo) {
        $this->pdo = $pdo;
    }
}
class chat extends dbo {
    function post($user, $message) {
        $sql = $this->pdo->prepare('INSERT INTO chat (time, user, message) VALUES (?, ?, ?)');
        return $sql->execute(array(date_format(date_create(), DATE_ISO8601), $user, $message));
    }
    function poll($limit) {
        $sql = $this->pdo->prepare('SELECT * FROM chat ORDER BY time DESC LIMIT ?');
        $sql->execute(array($limit));
        return array_reverse($sql->fetchAll(PDO::FETCH_ASSOC));
    }
}
class install extends dbo {
    function tables() {
        $sql =<<<'EOT'
            CREATE TABLE teams (
                name            TEXT        NOT NULL,
                abbr            TEXT        NOT NULL,
                ranking         INTEGER,
                CONSTRAINT pk_teams         PRIMARY KEY (name)
            );

            CREATE TABLE games (
                id              INTEGER     NOT NULL,
                home            TEXT        NOT NULL,
                home_goals      INTEGER,
                road            TEXT        NOT NULL,
                road_goals      INTEGER,
                time            TEXT        NOT NULL,
                CONSTRAINT pk_games         PRIMARY KEY (id),
                CONSTRAINT fk_teams_home    FOREIGN KEY (home) REFERENCES teams (name),
                CONSTRAINT fk_teams_road    FOREIGN KEY (road) REFERENCES teams (name)
            );

            CREATE TABLE scorers (
                name            TEXT        NOT NULL,
                team            TEXT        NOT NULL,
                number          INTEGER,
                goals           INTEGER     NOT NULL,
                CONSTRAINT pk_scorers       PRIMARY KEY (name),
                CONSTRAINT fk_teams         FOREIGN KEY (team) REFERENCES teams (name)
            );

            CREATE TABLE users (
                username        TEXT        NOT NULL,
                password        TEXT        NOT NULL,
                email           TEXT,
                active          INTEGER     NOT NULL,
                level           INTEGER     NOT NULL,
                visited         TEXT,
                CONSTRAINT pk_users         PRIMARY KEY (username)
            );

            CREATE TABLE remember (
                id              INTEGER     NOT NULL,
                random          INTEGER     NOT NULL,
                user            TEXT        NOT NULL,
                CONSTRAINT pk_remember      PRIMARY KEY (id),
                CONSTRAINT fk_users         FOREIGN KEY (user) REFERENCES users (username)
            );

            CREATE TABLE gamebets (
                game            INTEGER     NOT NULL,
                user            TEXT        NOT NULL,
                score           TEXT        NOT NULL,
                points          REAL,
                CONSTRAINT pk_gamebets      PRIMARY KEY (game, user),
                CONSTRAINT fk_games         FOREIGN KEY (game) REFERENCES games (id),
                CONSTRAINT fk_users         FOREIGN KEY (user) REFERENCES users (username)
            );

            CREATE TABLE singlebets (
                user            TEXT        NOT NULL,
                winner          TEXT,
                second          TEXT,
                third           TEXT,
                scorer          TEXT,
                CONSTRAINT pk_singlebets    PRIMARY KEY (user),
                CONSTRAINT fk_users         FOREIGN KEY (user)   REFERENCES users (username),
                CONSTRAINT fk_teams_winner  FOREIGN KEY (winner) REFERENCES teams (name),
                CONSTRAINT fk_teams_second  FOREIGN KEY (second) REFERENCES teams (name),
                CONSTRAINT fk_teams_third   FOREIGN KEY (third)  REFERENCES teams (name),
                CONSTRAINT fk_scorers       FOREIGN KEY (scorer) REFERENCES scorers (name)
            );

            CREATE TABLE chat (
                id              INTEGER     NOT NULL,
                time            TEXT        NOT NULL,
                user            TEXT        NOT NULL,
                message         TEXT        NOT NULL,
                CONSTRAINT pk_chat          PRIMARY KEY (id),
                CONSTRAINT fk_users         FOREIGN KEY (user) REFERENCES users (username)
            );
EOT;

        $this->pdo->exec($sql);
    }
}