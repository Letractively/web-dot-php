<?php
class chat extends dbo {
    function post($user, $message) {
        $stm = $this->db->prepare('INSERT INTO chat (time, user, message) VALUES (:time, :user, :message)');
        $stm->bindValue(':time', date_format(date_create(), DATE_ISO8601), SQLITE3_TEXT);
        $stm->bindValue(':user', $user, SQLITE3_TEXT);
        $stm->bindValue(':message', $message, SQLITE3_TEXT);
        $stm->execute();
        $stm->close();
    }
    function poll($limit) {
        $stm = $this->db->prepare('SELECT * FROM chat ORDER BY time DESC LIMIT :limit');
        $stm->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $res = $stm->execute();
        $messages = array();
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) $messages[] = $row;
        $stm->close();
        $res->finalize();
        return array_reverse($messages);
    }
}