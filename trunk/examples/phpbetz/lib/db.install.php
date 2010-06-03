<?php
namespace db\install {
    function tables() {
        $sql =<<<'SQL'
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
            password        TEXT,
            claim           TEXT,
            email           TEXT,
            active          INTEGER     NOT NULL,
            admin           INTEGER     NOT NULL,
            visited_time    TEXT,
            visited_page    TEXT,
            CONSTRAINT pk_users         PRIMARY KEY (username)
        );

        DROP INDEX IF EXISTS idx_users_email;
        CREATE UNIQUE INDEX idx_users_email ON users (email);

        DROP INDEX IF EXISTS idx_users_claim;
        CREATE UNIQUE INDEX idx_users_claim ON users (claim);

        DROP TABLE IF EXISTS remember;
        CREATE TABLE remember (
            user            TEXT        NOT NULL,
            key             TEXT        NOT NULL,
            expire          TEXT        NOT NULL,
            CONSTRAINT pk_remember      PRIMARY KEY (key, user),
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
SQL;
        $db = new \SQLite3(database, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        $db->exec($sql);
        $db->close();
    }
    function views() {
        $sql =<<<'SQL'
        DROP VIEW IF EXISTS view_games;
        CREATE VIEW view_games AS
        SELECT
            g.id AS id,
            g.time AS time,
            g.home AS home,
            h.abbr AS home_abbr,
            g.road AS road,
            r.abbr AS road_abbr
        FROM
            games AS g
        INNER JOIN
            teams AS h
        ON
            g.home = h.name
        INNER JOIN
            teams AS r
        ON
            g.road = r.name;

        DROP VIEW IF EXISTS view_singlebets;
        CREATE VIEW view_singlebets AS
        SELECT
            s.user,
            s.winner,
            t.abbr AS winner_abbr,
            s.second,
            t2.abbr AS second_abbr,
            s.third,
            t3.abbr AS third_abbr,
            s.scorer
        FROM
            singlebets s
        LEFT OUTER JOIN
            teams t
        ON
            s.winner = t.name
        LEFT OUTER JOIN
            teams t2
        ON
            s.second = t2.name
        LEFT OUTER JOIN
            teams t3
        ON
            s.third = t3.name;
SQL;
        $db = new \SQLite3(database, SQLITE3_OPEN_READWRITE);
        $db->exec($sql);
        $db->close();
    }
    function teams() {
        $sql =<<<'SQL'
        INSERT INTO teams (name, abbr) VALUES ('Algeria', 'ALG');
        INSERT INTO teams (name, abbr) VALUES ('Argentiina', 'ARG');
        INSERT INTO teams (name, abbr) VALUES ('Australia', 'AUS');
        INSERT INTO teams (name, abbr) VALUES ('Brasilia', 'BRA');
        INSERT INTO teams (name, abbr) VALUES ('Kamerun', 'CMR');
        INSERT INTO teams (name, abbr) VALUES ('Chile', 'CHI');
        INSERT INTO teams (name, abbr) VALUES ('Norsunluurannikko', 'CIV');
        INSERT INTO teams (name, abbr) VALUES ('Tanska', 'DEN');
        INSERT INTO teams (name, abbr) VALUES ('Englanti', 'ENG');
        INSERT INTO teams (name, abbr) VALUES ('Ranska', 'FRA');
        INSERT INTO teams (name, abbr) VALUES ('Saksa', 'GER');
        INSERT INTO teams (name, abbr) VALUES ('Ghana', 'GHA');
        INSERT INTO teams (name, abbr) VALUES ('Kreikka', 'GRE');
        INSERT INTO teams (name, abbr) VALUES ('Honduras', 'HON');
        INSERT INTO teams (name, abbr) VALUES ('Italia', 'ITA');
        INSERT INTO teams (name, abbr) VALUES ('Japani', 'JPN');
        INSERT INTO teams (name, abbr) VALUES ('Pohjois-Korea', 'PRK');
        INSERT INTO teams (name, abbr) VALUES ('Etelä-Korea', 'KOR');
        INSERT INTO teams (name, abbr) VALUES ('Meksiko', 'MEX');
        INSERT INTO teams (name, abbr) VALUES ('Hollanti', 'NED');
        INSERT INTO teams (name, abbr) VALUES ('Uusi-Seelanti', 'NZL');
        INSERT INTO teams (name, abbr) VALUES ('Nigeria', 'NGA');
        INSERT INTO teams (name, abbr) VALUES ('Paraguay', 'PAR');
        INSERT INTO teams (name, abbr) VALUES ('Portugali', 'POR');
        INSERT INTO teams (name, abbr) VALUES ('Serbia', 'SRB');
        INSERT INTO teams (name, abbr) VALUES ('Slovakia', 'SVK');
        INSERT INTO teams (name, abbr) VALUES ('Slovenia', 'SVN');
        INSERT INTO teams (name, abbr) VALUES ('Etelä-Afrikka', 'RSA');
        INSERT INTO teams (name, abbr) VALUES ('Espanja', 'ESP');
        INSERT INTO teams (name, abbr) VALUES ('Sveitsi', 'SUI');
        INSERT INTO teams (name, abbr) VALUES ('Uruguay', 'URU');
        INSERT INTO teams (name, abbr) VALUES ('USA', 'USA');
SQL;
        $db = new \SQLite3(database, SQLITE3_OPEN_READWRITE);
        $db->exec($sql);
        $db->close();
    }
    function games() {
        $sql =<<<'SQL'
        INSERT INTO games (home, road, time) VALUES ('Etelä-Afrikka', 'Meksiko', '2010-06-11T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Uruguay', 'Ranska', '2010-06-11T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Argentiina', 'Nigeria', '2010-06-12T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Etelä-Korea', 'Kreikka', '2010-06-12T14:30:00');
        INSERT INTO games (home, road, time) VALUES ('Englanti', 'USA', '2010-06-12T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Algeria', 'Slovenia', '2010-06-13T14:30:00');
        INSERT INTO games (home, road, time) VALUES ('Saksa', 'Australia', '2010-06-13T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Serbia', 'Ghana', '2010-06-13T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Hollanti', 'Tanska', '2010-06-14T14:30:00');
        INSERT INTO games (home, road, time) VALUES ('Japani', 'Kamerun', '2010-06-14T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Italia', 'Paraguay', '2010-06-14T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Uusi-Seelanti', 'Slovakia', '2010-06-15T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Norsunluurannikko', 'Portugali', '2010-06-15T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Brasilia', 'Pohjois-Korea', '2010-06-15T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Honduras', 'Chile', '2010-06-16T14:30:00');
        INSERT INTO games (home, road, time) VALUES ('Espanja', 'Sveitsi', '2010-06-16T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Etelä-Afrikka', 'Uruguay', '2010-06-16T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Ranska', 'Meksiko', '2010-06-17T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Kreikka', 'Nigeria', '2010-06-17T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Argentiina', 'Etelä-Korea', '2010-06-17T14:30:00');
        INSERT INTO games (home, road, time) VALUES ('Saksa', 'Serbia', '2010-06-18T14:30:00');
        INSERT INTO games (home, road, time) VALUES ('Slovenia', 'USA', '2010-06-18T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Englanti', 'Algeria', '2010-06-18T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Ghana', 'Australia', '2010-06-19T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Hollanti', 'Japani', '2010-06-19T14:30:00');
        INSERT INTO games (home, road, time) VALUES ('Kamerun', 'Tanska', '2010-06-19T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Slovakia', 'Paraguay', '2010-06-20T14:30:00');
        INSERT INTO games (home, road, time) VALUES ('Italia', 'Uusi-Seelanti', '2010-06-20T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Brasilia', 'Norsunluurannikko', '2010-06-20T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Portugali', 'Pohjois-Korea', '2010-06-21T14:30:00');
        INSERT INTO games (home, road, time) VALUES ('Chile', 'Sveitsi', '2010-06-21T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Espanja', 'Honduras', '2010-06-21T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Meksiko', 'Uruguay', '2010-06-22T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Ranska', 'Etelä-Afrikka', '2010-06-22T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Nigeria', 'Etelä-Korea', '2010-06-22T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Kreikka', 'Argentiina', '2010-06-22T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Slovenia', 'Englanti', '2010-06-23T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('USA', 'Algeria', '2010-06-23T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Ghana', 'Saksa', '2010-06-23T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Australia', 'Serbia', '2010-06-23T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Tanska', 'Japani', '2010-06-24T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Kamerun', 'Hollanti', '2010-06-24T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Slovakia', 'Italia', '2010-06-24T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Paraguay', 'Uusi-Seelanti', '2010-06-24T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Portugali', 'Brasilia', '2010-06-25T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Pohjois-Korea', 'Norsunluurannikko', '2010-06-25T17:00:00');
        INSERT INTO games (home, road, time) VALUES ('Chile', 'Espanja', '2010-06-25T21:30:00');
        INSERT INTO games (home, road, time) VALUES ('Sveitsi', 'Honduras', '2010-06-25T21:30:00');
SQL;
        $db = new \SQLite3(database, SQLITE3_OPEN_READWRITE);
        $db->exec($sql);
        $db->close();
    }
}
