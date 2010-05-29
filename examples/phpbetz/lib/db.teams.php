<?php
class teams extends dbo {
    function all() {
        return $this->db->query('SELECT * FROM teams ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
    }
}
