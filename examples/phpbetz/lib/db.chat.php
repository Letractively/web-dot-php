<?php
class chat extends dbo {
    function post($user, $message) {
        $sql = $this->db->prepare('INSERT INTO chat (time, user, message) VALUES (?, ?, ?)');
        return $sql->execute(array(date_format(date_create(), DATE_ISO8601), $user, $message));
    }
    function poll($limit) {
        $sql = $this->db->prepare('SELECT * FROM chat ORDER BY time DESC LIMIT ?');
        $sql->execute(array($limit));
        return array_reverse($sql->fetchAll(PDO::FETCH_ASSOC));
    }
}