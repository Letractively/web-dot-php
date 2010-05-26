<?php
class db extends PDO {
    public $chat, $teams, $install;
    function __construct() {
        parent::__construct(sprintf('sqlite:%s/db/phpbetz.sq3', __DIR__));
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
class teams extends dbo {
    function all() {
        return $this->db->query('SELECT * FROM teams ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
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

    }
    /*
1	11/06 17:00	Johannesburg - JSC		South Africa	Background	Mexico
2	11/06 21:30	Cape Town		Uruguay	Background	France
17	16/06 21:30	Tshwane/Pretoria		South Africa	Background	Uruguay
18	17/06 21:30	Polokwane		France	Background	Mexico
33	22/06 17:00	Rustenburg		Mexico	Background	Uruguay
34	22/06 17:00	Mangaung / Bloemfontein		France	Background	South Africa
Group B
Match	Date - Time	Venue			Results
3	12/06 17:00	Johannesburg - JEP		Argentina	Background	Nigeria
4	12/06 14:30	Nelson Mandela Bay/Port Elizabeth		Korea Republic	Background	Greece
19	17/06 17:00	Mangaung / Bloemfontein		Greece	Background	Nigeria
20	17/06 14:30	Johannesburg - JSC		Argentina	Background	Korea Republic
35	22/06 21:30	Durban		Nigeria	Background	Korea Republic
36	22/06 21:30	Polokwane		Greece	Background	Argentina
Group C
Match	Date - Time	Venue			Results
5	12/06 21:30	Rustenburg		England	Background	USA
6	13/06 14:30	Polokwane		Algeria	Background	Slovenia
22	18/06 17:00	Johannesburg - JEP		Slovenia	Background	USA
23	18/06 21:30	Cape Town		England	Background	Algeria
37	23/06 17:00	Nelson Mandela Bay/Port Elizabeth		Slovenia	Background	England
38	23/06 17:00	Tshwane/Pretoria		USA	Background	Algeria
Group D
Match	Date - Time	Venue			Results
7	13/06 21:30	Durban		Germany	Background	Australia
8	13/06 17:00	Tshwane/Pretoria		Serbia	Background	Ghana
21	18/06 14:30	Nelson Mandela Bay/Port Elizabeth		Germany	Background	Serbia
24	19/06 17:00	Rustenburg		Ghana	Background	Australia
39	23/06 21:30	Johannesburg - JSC		Ghana	Background	Germany
40	23/06 21:30	Nelspruit		Australia	Background	Serbia
Group E
Match	Date - Time	Venue			Results
9	14/06 14:30	Johannesburg - JSC		Netherlands	Background	Denmark
10	14/06 17:00	Mangaung / Bloemfontein		Japan	Background	Cameroon
25	19/06 14:30	Durban		Netherlands	Background	Japan
26	19/06 21:30	Tshwane/Pretoria		Cameroon	Background	Denmark
43	24/06 21:30	Rustenburg		Denmark	Background	Japan
44	24/06 21:30	Cape Town		Cameroon	Background	Netherlands
Group F
Match	Date - Time	Venue			Results
11	14/06 21:30	Cape Town		Italy	Background	Paraguay
12	15/06 14:30	Rustenburg		New Zealand	Background	Slovakia
27	20/06 14:30	Mangaung / Bloemfontein		Slovakia	Background	Paraguay
28	20/06 17:00	Nelspruit		Italy	Background	New Zealand
41	24/06 17:00	Johannesburg - JEP		Slovakia	Background	Italy
42	24/06 17:00	Polokwane		Paraguay	Background	New Zealand
Group G
Match	Date - Time	Venue			Results
13	15/06 17:00	Nelson Mandela Bay/Port Elizabeth		Côte d'Ivoire	Background	Portugal
14	15/06 21:30	Johannesburg - JEP		Brazil	Background	Korea DPR
29	20/06 21:30	Johannesburg - JSC		Brazil	Background	Côte d'Ivoire
30	21/06 14:30	Cape Town		Portugal	Background	Korea DPR
45	25/06 17:00	Durban		Portugal	Background	Brazil
46	25/06 17:00	Nelspruit		Korea DPR	Background	Côte d'Ivoire
Group H
Match	Date - Time	Venue			Results
15	16/06 14:30	Nelspruit		Honduras	Background	Chile
16	16/06 17:00	Durban		Spain	Background	Switzerland
31	21/06 17:00	Nelson Mandela Bay/Port Elizabeth		Chile	Background	Switzerland
32	21/06 21:30	Johannesburg - JEP		Spain	Background	Honduras
47	25/06 21:30	Tshwane/Pretoria		Chile	Background	Spain
48	25/06 21:30	Mangaung / Bloemfontein		Switzerland	Background	Honduras
         */
}