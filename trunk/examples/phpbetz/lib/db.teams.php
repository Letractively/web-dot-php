<?php
namespace db\teams {
    function all() {
        $teams = array();
        $db = new \SQLite3(database, SQLITE3_OPEN_READONLY);
        $db->exec('PRAGMA synchronous = NORMAL;');
        $res = $db->query('SELECT * FROM teams ORDER BY name ASC');
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) $teams[] = $row;
        $res->finalize();
        $db->close();
        return $teams;
    }
    function exists($name) {
        $db = new \SQLite3(database, SQLITE3_OPEN_READONLY);
        $db->exec('PRAGMA synchronous = NORMAL;');
        $stm = $db->prepare('SELECT 1 FROM teams WHERE name = :name');
        $stm->bindValue(':name', $name, SQLITE3_TEXT);
        $res = $stm->execute();
        $row = $res->fetchArray(SQLITE3_NUM);
        $res->finalize();
        $stm->close();
        $db->close();
        return $row !== false;
    }
}
