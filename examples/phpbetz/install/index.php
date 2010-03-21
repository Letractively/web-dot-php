<?php

$starttime = microtime(true);

/* =======================================================================
 * Setup Default Timezone
 * ======================================================================= */

date_default_timezone_set('Europe/Helsinki');

/* =======================================================================
 * Setup Additional Include Paths and Register Class Auto Loader
 * ======================================================================= */

set_include_path('..' . DIRECTORY_SEPARATOR .
                 '..' . DIRECTORY_SEPARATOR .
                 '..' . DIRECTORY_SEPARATOR .
                 'src' . PATH_SEPARATOR . get_include_path());

/* =======================================================================
 * Include Required Files
 * ======================================================================= */

require 'Web.php';

/* =======================================================================
 * Enable Error Handling
 * ======================================================================= */

error_reporting(E_ALL | E_STRICT);

/* =======================================================================
 * Dispatch Request
 * ======================================================================= */

get('/', function ($params) {

    $tables =<<<'EOT'
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
            date            TEXT        NOT NULL,
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
EOT;


});