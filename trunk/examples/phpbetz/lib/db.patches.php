<?php
namespace db\patches {
    function view_games_1() {
        $sql =<<<'SQL'
        DROP VIEW IF EXISTS view_games;
        CREATE VIEW view_games AS
        SELECT
            g.id AS id,
            g.time AS time,
            g.draw AS draw,
            g.score AS score,
            g.home AS home,
            g.home_goals AS home_goals,
            g.home_percent AS home_percent,
            h.abbr AS home_abbr,
            g.road AS road,
            g.road_goals AS road_goals,
            g.road_percent AS road_percent,
            r.abbr AS road_abbr,
            g.draw_percent AS road_percent,
            g.points AS points
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
SQL;
        $db = new \SQLite3(database, SQLITE3_OPEN_READWRITE);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        $db->exec($sql);
        $db->close();
    }
    function view_games_2() {
        $sql =<<<'SQL'
        DROP VIEW IF EXISTS view_games;
        CREATE VIEW view_games AS
        SELECT
            g.id AS id,
            g.time AS time,
            g.draw AS draw,
            g.draw_percent AS draw_percent,
            g.score AS score,
            g.home AS home,
            g.home_goals AS home_goals,
            g.home_percent AS home_percent,
            h.abbr AS home_abbr,
            g.road AS road,
            g.road_goals AS road_goals,
            g.road_percent AS road_percent,
            r.abbr AS road_abbr,
            g.points AS points
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
SQL;
        $db = new \SQLite3(database, SQLITE3_OPEN_READWRITE);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        $db->exec($sql);
        $db->close();
    }
}