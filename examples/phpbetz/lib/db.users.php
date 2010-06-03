<?php
class users extends dbo {
    function login($username, $password) {
        $stm = $this->db->prepare('SELECT COUNT(*) FROM users WHERE username = :username AND password = :password AND active = :active');
        $stm->bindValue(':username', $username, SQLITE3_TEXT);
        $stm->bindValue(':password', $password, SQLITE3_TEXT);
        $stm->bindValue(':active', 1, SQLITE3_INTEGER);
        $res = $stm->execute();
        $row = $res->fetchArray(SQLITE3_NUM);
        $res->finalize();
        $stm->close();
        return $row && $row[0] === 1;
    }
    function authenticate($username) {
        $stm = $this->db->prepare('SELECT * FROM users WHERE username = :username AND active = :active');
        $stm->bindValue(':username', $username, SQLITE3_TEXT);
        $stm->bindValue(':active', 1, SQLITE3_INTEGER);
        $res = $stm->execute();
        $row = $res->fetchArray(SQLITE3_ASSOC);
        $res->finalize();
        $stm->close();
        return $row;
    }
    function register($username, $password, $email) {
        $stm = $this->db->prepare('INSERT OR IGNORE INTO users (username, password, email, active, admin) VALUES (:username, :password, :email, :active, :admin)');
        $stm->bindValue(':username', $username, SQLITE3_TEXT);
        $stm->bindValue(':password', $password, SQLITE3_TEXT);
        $stm->bindValue(':email', $email, SQLITE3_TEXT);
        $stm->bindValue(':active', 1, SQLITE3_INTEGER);
        $stm->bindValue(':admin', 0, SQLITE3_INTEGER);
        $stm->execute();
        $stm->close();
    }
    function claim($username, $claim, $email) {
        $stm = $this->db->prepare('INSERT OR IGNORE INTO users (username, claim, email, active, admin) VALUES (:username, :claim, :email, :active, :admin)');
        $stm->bindValue(':username', $username, SQLITE3_TEXT);
        $stm->bindValue(':claim', $claim, SQLITE3_TEXT);
        $stm->bindValue(':email', $email, SQLITE3_TEXT);
        $stm->bindValue(':active', 1, SQLITE3_INTEGER);
        $stm->bindValue(':admin', 0, SQLITE3_INTEGER);
        $stm->execute();
        $stm->close();
    }
    function claimed($claim) {
        $stm = $this->db->prepare('SELECT username FROM users WHERE claim = :claim');
        $stm->bindValue(':claim', $claim, SQLITE3_TEXT);
        $res = $stm->execute();
        $row = $res->fetchArray(SQLITE3_NUM);
        $res->finalize();
        $stm->close();
        if ($row === false) return false;
        return $row[0];
    }
    function username_taken($username) {
        $stm = $this->db->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
        $stm->bindValue(':username', $username, SQLITE3_TEXT);
        $res = $stm->execute();
        $row = $res->fetchArray(SQLITE3_NUM);
        $res->finalize();
        $stm->close();
        return $row && $row[0] === 1;
    }
    function email_taken($email) {
        $stm = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stm->bindValue(':email', $email, SQLITE3_TEXT);
        $res = $stm->execute();
        $row = $res->fetchArray(SQLITE3_NUM);
        $res->finalize();
        $stm->close();
        return $row && $row[0] === 1;
    }
    function remember($username, $key, $expire) {
        $stm = $this->db->prepare('INSERT OR REPLACE INTO remember (user, key, expire) VALUES (:user, :key, :expire)');
        $stm->bindValue(':user', $username, SQLITE3_TEXT);
        $stm->bindValue(':key', $key, SQLITE3_TEXT);
        $stm->bindValue(':expire', $expire, SQLITE3_TEXT);
        $stm->execute();
        $stm->close();
    }
    function forget($username, $key) {
        $stm = $this->db->prepare('DELETE FROM remember WHERE user = :user AND key = :key)');
        $stm->bindValue(':user', $username, SQLITE3_TEXT);
        $stm->bindValue(':key', $key, SQLITE3_TEXT);
        $stm->execute();
        $stm->close();
    }
    function remembered($username, $key) {
        $stm = $this->db->prepare('DELETE FROM remember WHERE user = :user AND key = :key AND expire > :expire');
        $stm->bindValue(':user', $username, SQLITE3_TEXT);
        $stm->bindValue(':key', $key, SQLITE3_TEXT);
        $stm->bindValue(':expire', date_format(date_create(), DATE_SQLITE), SQLITE3_TEXT);
        $stm->execute();
        $stm->close();
        $changes = $this->db->changes();
        if ($changes > 0) return $this->authenticate($username);
        return false;
    }
}