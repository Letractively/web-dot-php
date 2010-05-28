<?php
class db extends PDO {
    public $chat, $teams, $bets, $install, $news;
    function __construct() {
        parent::__construct('sqlite:' . realpath(__DIR__ . '/../data/phpbetz.sq3'));
        $this->chat = new chat($this);
        $this->teams = new teams($this);
        $this->bets = new bets($this);
        $this->install = new install($this);
        $this->news = new news($this);
    }
}
abstract class dbo {
    protected $db;
    function __construct(db $db) {
        $this->db = $db;
    }
}
class chat extends dbo {
    function post($user, $message) {
        $sql = $this->db->prepare('INSERT INTO chat (time, user, message) VALUES (?, ?, ?)');
        return $sql->execute(array(date_format(date_create(), DATE_ISO8601), $user, $message));
    }
    function poll($limit) {
        $sql = $this->db->prepare('SELECT * FROM chat ORDER BY time DESC LIMIT ?');
        $sql->execute(array($limit));
        return array_reverse($sql->fetchAll(PDO::FETCH_ASSOC));
    }
}
class news extends dbo {
    function all() {
        return $this->db->query('SELECT * FROM news ORDER BY time DESC')->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function add($title, $content, $level, $user, $slug) {
        $sql = $this->db->prepare('INSERT INTO news (time, title, content, level, user, slug) VALUES (?, ?, ?, ?, ?, ?)');
        return $sql->execute(array(date_format(date_create(), DATE_ISO8601), $title, $content, $level, $user, $slug));
    }
}
class teams extends dbo {
    function all() {
        return $this->db->query('SELECT * FROM teams ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
    }
}
class bets extends dbo {
    function games($user) {
        $sql =<<< 'EOT'
    SELECT
        g.id AS id,
        g.time AS time,
        g.home AS home,
        g.road AS road,
        b.score as score,
        b.user
    FROM
        games AS g
    LEFT OUTER JOIN
        gamebets AS b
    ON
        g.id = b.game AND b.user = ?
    ORDER BY
        time;
EOT;
        $sql = $this->db->prepare($sql);
        $sql->execute(array($user));
        $sql->fetchAll(PDO::FETCH_ASSOC);
    }
}
class install extends dbo {
    function tables() {
        $sql =<<<'EOT'
            DROP TABLE IF EXISTS teams;
            CREATE TABLE teams (
                name            TEXT        NOT NULL,
                abbr            TEXT        NOT NULL,
                ranking         INTEGER,
                CONSTRAINT pk_teams         PRIMARY KEY (name)
            );

            DROP TABLE IF EXISTS games;
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

            DROP TABLE IF EXISTS scorers;
            CREATE TABLE scorers (
                name            TEXT        NOT NULL,
                team            TEXT        NOT NULL,
                number          INTEGER,
                goals           INTEGER     NOT NULL,
                CONSTRAINT pk_scorers       PRIMARY KEY (name),
                CONSTRAINT fk_teams         FOREIGN KEY (team) REFERENCES teams (name)
            );

            DROP TABLE IF EXISTS users;
            CREATE TABLE users (
                username        TEXT        NOT NULL,
                password        TEXT        NOT NULL,
                email           TEXT,
                active          INTEGER     NOT NULL,
                level           INTEGER     NOT NULL,
                visited         TEXT,
                CONSTRAINT pk_users         PRIMARY KEY (username)
            );

            DROP TABLE IF EXISTS remember;
            CREATE TABLE remember (
                id              INTEGER     NOT NULL,
                random          INTEGER     NOT NULL,
                user            TEXT        NOT NULL,
                CONSTRAINT pk_remember      PRIMARY KEY (id),
                CONSTRAINT fk_users         FOREIGN KEY (user) REFERENCES users (username)
            );

            DROP TABLE IF EXISTS gamebets;
            CREATE TABLE gamebets (
                game            INTEGER     NOT NULL,
                user            TEXT        NOT NULL,
                score           TEXT        NOT NULL,
                points          REAL,
                CONSTRAINT pk_gamebets      PRIMARY KEY (game, user),
                CONSTRAINT fk_games         FOREIGN KEY (game) REFERENCES games (id),
                CONSTRAINT fk_users         FOREIGN KEY (user) REFERENCES users (username)
            );

            DROP TABLE IF EXISTS singlebets;
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

            DROP TABLE IF EXISTS news;
            CREATE TABLE news (
                id              INTEGER     NOT NULL,
                time            TEXT        NOT NULL,
                user            TEXT        NOT NULL,
                title           TEXT        NOT NULL,
                content         TEXT        NOT NULL,
                level           INTEGER     NOT NULL,
                slug            TEXT        NOT NULL,
                CONSTRAINT pk_news          PRIMARY KEY (id),
                CONSTRAINT fk_users         FOREIGN KEY (user) REFERENCES users (username)
            );
            
            DROP TABLE IF EXISTS chat;
            CREATE TABLE chat (
                id              INTEGER     NOT NULL,
                time            TEXT        NOT NULL,
                user            TEXT        NOT NULL,
                message         TEXT        NOT NULL,
                CONSTRAINT pk_chat          PRIMARY KEY (id),
                CONSTRAINT fk_users         FOREIGN KEY (user) REFERENCES users (username)
            );
EOT;
        $this->db->exec($sql);
    }
    function teams() {
        $sql = $this->db->prepare('INSERT INTO teams (name, abbr) VALUES (?, ?)');
        $sql->execute(array('Algeria', 'ALG'));
        $sql->execute(array('Argentiina', 'ARG'));
        $sql->execute(array('Australia', 'AUS'));
        $sql->execute(array('Brasilia', 'BRA'));
        $sql->execute(array('Kamerun', 'CMR'));
        $sql->execute(array('Chile', 'CHI'));
        $sql->execute(array('Norsunluurannikko', 'CIV'));
        $sql->execute(array('Tanska', 'DEN'));
        $sql->execute(array('Englanti', 'ENG'));
        $sql->execute(array('Ranska', 'FRA'));
        $sql->execute(array('Saksa', 'GER'));
        $sql->execute(array('Ghana', 'GHA'));
        $sql->execute(array('Kreikka', 'GRE'));
        $sql->execute(array('Honduras', 'HON'));
        $sql->execute(array('Italia', 'ITA'));
        $sql->execute(array('Japani', 'JPN'));
        $sql->execute(array('Pohjois-Korea', 'PRK'));
        $sql->execute(array('Etelä-Korea', 'KOR'));
        $sql->execute(array('Meksiko', 'MEX'));
        $sql->execute(array('Hollanti', 'NED'));
        $sql->execute(array('Uusi-Seelanti', 'NZL'));
        $sql->execute(array('Nigeria', 'NGA'));
        $sql->execute(array('Paraguay', 'PAR'));
        $sql->execute(array('Portugali', 'POR'));
        $sql->execute(array('Serbia', 'SRB'));
        $sql->execute(array('Slovakia', 'SVK'));
        $sql->execute(array('Slovenia', 'SVN'));
        $sql->execute(array('Etelä-Afrikka', 'RSA'));
        $sql->execute(array('Espanja', 'ESP'));
        $sql->execute(array('Sveitsi', 'SUI'));
        $sql->execute(array('Uruguay', 'URU'));
        $sql->execute(array('USA', 'USA'));
    }
    function games() {
        $sql = $this->db->prepare('INSERT INTO games (home, road, time) VALUES (?, ?, ?)');
        $sql->execute(array('Etelä-Afrikka', 'Meksiko', '2010-06-11T17:00:00+0300'));
        $sql->execute(array('Uruguay', 'Ranska', '2010-06-11T21:30:00+0300'));
        $sql->execute(array('Argentiina', 'Nigeria', '2010-06-12T17:00:00+0300'));
        $sql->execute(array('Etelä-Korea', 'Kreikka', '2010-06-12T14:30:00+0300'));
        $sql->execute(array('Englanti', 'USA', '2010-06-12T21:30:00+0300'));
        $sql->execute(array('Algeria', 'Slovenia', '2010-06-13T14:30:00+0300'));
        $sql->execute(array('Saksa', 'Australia', '2010-06-13T21:30:00+0300'));
        $sql->execute(array('Serbia', 'Ghana', '2010-06-13T17:00:00+0300'));
        $sql->execute(array('Hollanti', 'Tanska', '2010-06-14T14:30:00+0300'));
        $sql->execute(array('Japani', 'Kamerun', '2010-06-14T17:00:00+0300'));
        $sql->execute(array('Italia', 'Paraguay', '2010-06-14T21:30:00+0300'));
        $sql->execute(array('Uusi-Seelanti', 'Slovakia', '2010-06-15T21:30:00+0300'));
        $sql->execute(array('Norsunluurannikko', 'Portugali', '2010-06-15T17:00:00+0300'));
        $sql->execute(array('Brasilia', 'Pohjois-Korea', '2010-06-15T21:30:00+0300'));
        $sql->execute(array('Honduras', 'Chile', '2010-06-16T14:30:00+0300'));
        $sql->execute(array('Espanja', 'Sveitsi', '2010-06-16T17:00:00+0300'));
        $sql->execute(array('Etelä-Afrikka', 'Uruguay', '2010-06-16T21:30:00+0300'));
        $sql->execute(array('Ranska', 'Meksiko', '2010-06-17T21:30:00+0300'));
        $sql->execute(array('Kreikka', 'Nigeria', '2010-06-17T17:00:00+0300'));
        $sql->execute(array('Argentiina', 'Etelä-Korea', '2010-06-17T14:30:00+0300'));
        $sql->execute(array('Saksa', 'Serbia', '2010-06-18T14:30:00+0300'));
        $sql->execute(array('Slovenia', 'USA', '2010-06-18T17:00:00+0300'));
        $sql->execute(array('Englanti', 'Algeria', '2010-06-18T21:30:00+0300'));
        $sql->execute(array('Ghana', 'Australia', '2010-06-19T17:00:00+0300'));
        $sql->execute(array('Hollanti', 'Japani', '2010-06-19T14:30:00+0300'));
        $sql->execute(array('Kamerun', 'Tanska', '2010-06-19T21:30:00+0300'));
        $sql->execute(array('Slovakia', 'Paraguay', '2010-06-20T14:30:00+0300'));
        $sql->execute(array('Italia', 'Uusi-Seelanti', '2010-06-20T17:00:00+0300'));
        $sql->execute(array('Brasilia', 'Norsunluurannikko', '2010-06-20T21:30:00+0300'));
        $sql->execute(array('Portugali', 'Pohjois-Korea', '2010-06-21T14:30:00+0300'));
        $sql->execute(array('Chile', 'Sveitsi', '2010-06-21T17:00:00+0300'));
        $sql->execute(array('Espanja', 'Honduras', '2010-06-21T21:30:00+0300'));
        $sql->execute(array('Meksiko', 'Uruguay', '2010-06-22T17:00:00+0300'));
        $sql->execute(array('Ranska', 'Etelä-Afrikka', '2010-06-22T17:00:00+0300'));
        $sql->execute(array('Nigeria', 'Etelä-Korea', '2010-06-22T21:30:00+0300'));
        $sql->execute(array('Kreikka', 'Argentiina', '2010-06-22T21:30:00+0300'));
        $sql->execute(array('Slovenia', 'Englanti', '2010-06-23T17:00:00+0300'));
        $sql->execute(array('USA', 'Algeria', '2010-06-23T17:00:00+0300'));
        $sql->execute(array('Ghana', 'Saksa', '2010-06-23T21:30:00+0300'));
        $sql->execute(array('Australia', 'Serbia', '2010-06-23T21:30:00+0300'));
        $sql->execute(array('Tanska', 'Japani', '2010-06-24T21:30:00+0300'));
        $sql->execute(array('Kamerun', 'Hollanti', '2010-06-24T21:30:00+0300'));
        $sql->execute(array('Slovakia', 'Italia', '2010-06-24T17:00:00+0300'));
        $sql->execute(array('Paraguay', 'Uusi-Seelanti', '2010-06-24T17:00:00+0300'));
        $sql->execute(array('Portugali', 'Brasilia', '2010-06-25T17:00:00+0300'));
        $sql->execute(array('Pohjois-Korea', 'Norsunluurannikko', '2010-06-25T17:00:00+0300'));
        $sql->execute(array('Chile', 'Espanja', '2010-06-25T21:30:00+0300'));
        $sql->execute(array('Sveitsi', 'Honduras', '2010-06-25T21:30:00+0300'));
    }
}