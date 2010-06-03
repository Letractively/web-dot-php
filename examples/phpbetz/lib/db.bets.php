<?php
namespace db\bets {
    function games($user) {
        $sql =<<< 'EOT'
    SELECT
        g.id AS id,
        g.time AS time,
        g.home AS home,
        h.abbr AS home_abbr,
        g.road AS road,
        r.abbr AS road_abbr,
        b.score as score,
        b.user
    FROM
        games AS g
    INNER JOIN
        teams AS h
    ON
        g.home = h.name
    INNER JOIN
        teams AS r
    ON
        g.road = r.name
    LEFT OUTER JOIN
        gamebets AS b
    ON
        g.id = b.game AND b.user = :user
    ORDER BY
        time, id;
EOT;
        $db = new \SQLite3(database, SQLITE3_OPEN_READONLY);
        $stm = $db->prepare($sql);
        $stm->bindValue(':user', $user, SQLITE3_TEXT);
        $res = $stm->execute();
        $games = array();
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) $games[] = $row;
        $res->finalize();
        $stm->close();
        $db->close();
        return $games;
    }

    function game($game, $user, $score) {
        $db = new \SQLite3(database, SQLITE3_OPEN_READWRITE);
        $stm = $db->prepare('INSERT OR REPLACE INTO gamebets (game, user, score) VALUES (:game, :user, :score)');
        $stm->bindValue(':game', $game, SQLITE3_INTEGER);
        $stm->bindValue(':user', $user, SQLITE3_TEXT);
        $stm->bindValue(':score', $score, SQLITE3_TEXT);
        $stm->execute();
        $stm->close();
        $db->close();
    }
}