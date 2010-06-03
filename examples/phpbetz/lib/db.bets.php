<?php
namespace db\bets {
    function games($user) {
        $sql =<<< 'SQL'
    SELECT * FROM
        view_games AS g
    LEFT OUTER JOIN
        gamebets AS b
    ON
        g.id = b.game AND b.user = :user
    ORDER BY
        time, id;
SQL;
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

    function single($user) {
        $db = new \SQLite3(database, SQLITE3_OPEN_READONLY);
        $stm = $db->prepare('SELECT * FROM view_singlebets WHERE user = :user');
        $stm->bindValue(':user', $user, SQLITE3_TEXT);
        $res = $stm->execute();
        $row = $res->fetchArray(SQLITE3_ASSOC);
        $res->finalize();
        $stm->close();
        $db->close();
        return $row;
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
    function winner($user, $team) {
        team($user, $team, 'winner');
    }
    function second($user, $team) {
        team($user, $team, 'second');
    }
    function third($user, $team) {
        team($user, $team, 'third');
    }
    function team($user, $team, $position) {
        $db = new \SQLite3(database, SQLITE3_OPEN_READWRITE);
        $stm = $db->prepare('UPDATE singlebets SET ' . $position . ' = :team WHERE user = :user');
        $stm->bindValue(':team', $team, SQLITE3_TEXT);
        $stm->bindValue(':user', $user, SQLITE3_TEXT);
        $stm->execute();
        $changes = $db->changes();
        $stm->close();
        if ($changes == 0) {
            $stm = $db->prepare('INSERT INTO singlebets (user, ' . $position . ') VALUES (:user, :team)');
            $stm->bindValue(':user', $user, SQLITE3_TEXT);
            $stm->bindValue(':team', $team, SQLITE3_TEXT);
            $stm->execute();
            $stm->close();
        }
        $db->close();
    }
}