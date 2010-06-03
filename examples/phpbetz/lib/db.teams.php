<?php
class teams extends dbo {
    function all() {
        $teams = array();
        $res = $this->db->query('SELECT * FROM teams ORDER BY name ASC');
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) $teams[] = $row;
        $res->finalize();
        return $teams;
    }
}
