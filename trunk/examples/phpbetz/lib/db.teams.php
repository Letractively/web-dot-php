<?php
class teams extends dbo {
    function all() {
        $res = $this->db->query('SELECT * FROM teams ORDER BY name ASC');
        $teams = array();
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) $teams[] = $row;
        $res->finalize();
        return $teams;
    }
}
