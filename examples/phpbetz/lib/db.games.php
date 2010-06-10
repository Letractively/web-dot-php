<?php
namespace db\games {
    function all() {
        $games = array();
        $db = new \SQLite3(database, SQLITE3_OPEN_READONLY);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        $res = $db->query('SELECT * FROM view_games ORDER BY time DESC');
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
}