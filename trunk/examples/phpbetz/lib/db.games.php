<?php
namespace db\games {
    function all() {
        $games = array();
        $db = new \SQLite3(database, SQLITE3_OPEN_READONLY);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        $res = $db->query('SELECT * FROM view_games ORDER BY time');
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) $games[] = $row;
        $res->finalize();
        $db->close();
        return $games;
    }
    function start() {
        $db = new \SQLite3(database, SQLITE3_OPEN_READONLY);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        $start = $db->querySingle('SELECT MIN(time) FROM games', false);
        $db->close();
        return $start;
    }
    function score($id, $home_goals, $road_goals) {
        $db = new \SQLite3(database, SQLITE3_OPEN_READWRITE);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        if ($home_goals === $road_goals) {
            $stm = $db->prepare('UPDATE games SET home_goals = :home, road_goals = :road, score = :score, points = (200.0 - draw_percent) / 100.0 WHERE id = :id');
            $stm->bindValue(':id', $id, SQLITE3_INTEGER);
            $stm->bindValue(':home', $home_goals, SQLITE3_INTEGER);
            $stm->bindValue(':road', $road_goals, SQLITE3_INTEGER);
            $stm->bindValue(':score', 'X', SQLITE3_TEXT);
            $stm->execute();
            $stm->close();
        } elseif ($home_goals > $road_goals) {
            $stm = $db->prepare('UPDATE games SET home_goals = :home, road_goals = :road, score = :score, points = (200.0 - home_percent) / 100.0 WHERE id = :id');
            $stm->bindValue(':id', $id, SQLITE3_INTEGER);
            $stm->bindValue(':home', $home_goals, SQLITE3_INTEGER);
            $stm->bindValue(':road', $road_goals, SQLITE3_INTEGER);
            $stm->bindValue(':score', '1', SQLITE3_TEXT);
            $stm->execute();
            $stm->close();
        } else {
            $stm = $db->prepare('UPDATE games SET home_goals = :home, road_goals = :road, score = :score, points = (200.0 - road_percent) / 100.0 WHERE id = :id');
            $stm->bindValue(':id', $id, SQLITE3_INTEGER);
            $stm->bindValue(':home', $home_goals, SQLITE3_INTEGER);
            $stm->bindValue(':road', $road_goals, SQLITE3_INTEGER);
            $stm->bindValue(':score', '2', SQLITE3_TEXT);
            $stm->execute();
            $stm->close();
        }
        $db->close();
    }
}