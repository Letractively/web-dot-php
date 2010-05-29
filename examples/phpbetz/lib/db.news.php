<?php
class news extends dbo {
    function all() {
        return $this->db->query('SELECT * FROM news ORDER BY time DESC')->fetchAll(PDO::FETCH_ASSOC);
    }

    function add($title, $content, $level, $user, $slug) {
        $sql = $this->db->prepare('INSERT INTO news (time, title, content, level, user, slug) VALUES (?, ?, ?, ?, ?, ?)');
        return $sql->execute(array(date_format(date_create(), DATE_ISO8601), $title, $content, $level, $user, $slug));
    }
}