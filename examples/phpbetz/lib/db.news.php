<?php
class news extends dbo {
    function all() {
        $res = $this->db->query('SELECT * FROM news ORDER BY time DESC');
        $news = array();
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) $news[] = $row;
        $res->finalize();
        return $news;
    }

    function add($title, $content, $level, $user, $slug) {
        $stm = $this->db->prepare('INSERT INTO news (time, slug, title, content, level, user) VALUES (:time, :slug, :title, :content, :level, :user)');
        $stm->bindValue(':time', date_format(date_create(), DATE_SQLITE), SQLITE3_TEXT);
        $stm->bindValue(':slug', $user, SQLITE3_TEXT);
        $stm->bindValue(':title', $title, SQLITE3_TEXT);
        $stm->bindValue(':content', $content, SQLITE3_TEXT);
        $stm->bindValue(':level', $level, SQLITE3_INTEGER);
        $stm->execute();
        $stm->close();
    }
}
