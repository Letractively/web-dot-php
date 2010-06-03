<?php
namespace db\teams {
    function all() {
        $teams = array();
        $db = new \SQLite3(database, SQLITE3_OPEN_READONLY);
        $res = $db->query('SELECT * FROM teams ORDER BY name ASC');
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) $teams[] = $row;
        $res->finalize();
        $db->close();
        return $teams;
    }
}
