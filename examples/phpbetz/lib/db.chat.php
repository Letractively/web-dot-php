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
    function latest($limit, &$last) {
        $stm = $this->db->prepare('SELECT * FROM chat ORDER BY id DESC LIMIT :limit');
        $stm->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $res = $stm->execute();
        $messages = array();
        $first = true;
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            if ($first) {
                $last = $row['id'];
                $first = false;
            }
            $messages[] = $row;
        }
        $res->finalize();
        $stm->close();
        return array_reverse($messages);
    }
    function poll(&$last) {
        $stm = $this->db->prepare('SELECT * FROM chat WHERE id > :id ORDER BY id');
        $stm->bindValue(':id', $last, SQLITE3_INTEGER);
        $res = $stm->execute();
        $messages = array();
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $last = $row['id'];
            $messages[] = $row;
        }
        $res->finalize();
        $stm->close();
        return $messages;
    }
}