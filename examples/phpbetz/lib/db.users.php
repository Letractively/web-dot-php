<?php
namespace db\users {
    function all() {
        $users = array();
        $db = new \SQLite3(database, SQLITE3_OPEN_READONLY);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        //$db->exec('PRAGMA synchronous = NORMAL');
        $res = $db->query('SELECT * FROM users ORDER BY username ASC');
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) $users[] = $row;
        $res->finalize();
        $db->close();
        return $users;
    }
    function login($username, $password) {
        $db = new \SQLite3(database, SQLITE3_OPEN_READONLY);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        //$db->exec('PRAGMA synchronous = NORMAL');
        $stm = $db->prepare('SELECT 1 FROM users WHERE username = :username AND password = :password AND active = :active');
        $stm->bindValue(':username', $username, SQLITE3_TEXT);
        $stm->bindValue(':password', $password, SQLITE3_TEXT);
        $stm->bindValue(':active', 1, SQLITE3_INTEGER);
        $res = $stm->execute();
        $row = $res->fetchArray(SQLITE3_NUM);
        $res->finalize();
        $stm->close();
        $db->close();
        return $row !== false;
    }
    function authenticate($username) {
        $db = new \SQLite3(database, SQLITE3_OPEN_READONLY);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        //$db->exec('PRAGMA synchronous = NORMAL');
        $stm = $db->prepare('SELECT * FROM users WHERE username = :username AND active = :active');
        $stm->bindValue(':username', $username, SQLITE3_TEXT);
        $stm->bindValue(':active', 1, SQLITE3_INTEGER);
        $res = $stm->execute();
        $row = $res->fetchArray(SQLITE3_ASSOC);
        $res->finalize();
        $stm->close();
        $db->close();
        return $row;
    }
    function register($username, $password, $email) {
        $db = new \SQLite3(database, SQLITE3_OPEN_READWRITE);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        //$db->exec('PRAGMA synchronous = NORMAL');
        $stm = $db->prepare('INSERT OR IGNORE INTO users (username, password, email, active, admin) VALUES (:username, :password, :email, :active, :admin)');
        $stm->bindValue(':username', $username, SQLITE3_TEXT);
        $stm->bindValue(':password', $password, SQLITE3_TEXT);
        $stm->bindValue(':email', $email, SQLITE3_TEXT);
        $stm->bindValue(':active', 1, SQLITE3_INTEGER);
        $stm->bindValue(':admin', 0, SQLITE3_INTEGER);
        $stm->execute();
        $changes = $db->changes();
        $stm->close();
        $db->close();
        return $changes;
    }
    function claim($username, $claim, $email) {
        $db = new \SQLite3(database, SQLITE3_OPEN_READWRITE);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        //$db->exec('PRAGMA synchronous = NORMAL');
        $stm = $db->prepare('INSERT OR IGNORE INTO users (username, claim, email, active, admin) VALUES (:username, :claim, :email, :active, :admin)');
        $stm->bindValue(':username', $username, SQLITE3_TEXT);
        $stm->bindValue(':claim', $claim, SQLITE3_TEXT);
        $stm->bindValue(':email', $email, SQLITE3_TEXT);
        $stm->bindValue(':active', 1, SQLITE3_INTEGER);
        $stm->bindValue(':admin', 0, SQLITE3_INTEGER);
        $stm->execute();
        $changes = $db->changes();
        $stm->close();
        $db->close();
        return $changes;
    }
    function claimed($claim) {
        $db = new \SQLite3(database, SQLITE3_OPEN_READONLY);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        //$db->exec('PRAGMA synchronous = NORMAL');
        $stm = $db->prepare('SELECT username FROM users WHERE claim = :claim');
        $stm->bindValue(':claim', $claim, SQLITE3_TEXT);
        $res = $stm->execute();
        $row = $res->fetchArray(SQLITE3_NUM);
        $res->finalize();
        $stm->close();
        $db->close();
        if ($row === false) return false;
        return $row[0];
    }
    function username_taken($username) {
        $db = new \SQLite3(database, SQLITE3_OPEN_READONLY);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        //$db->exec('PRAGMA synchronous = NORMAL');
        $stm = $db->prepare('SELECT 1 FROM users WHERE username = :username');
        $stm->bindValue(':username', $username, SQLITE3_TEXT);
        $res = $stm->execute();
        $row = $res->fetchArray(SQLITE3_NUM);
        $res->finalize();
        $stm->close();
        $db->close();
        return $row !== false;
    }
    function email_taken($email) {
        $db = new \SQLite3(database, SQLITE3_OPEN_READONLY);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        //$db->exec('PRAGMA synchronous = NORMAL');
        $stm = $db->prepare('SELECT 1 FROM users WHERE email = :email');
        $stm->bindValue(':email', $email, SQLITE3_TEXT);
        $res = $stm->execute();
        $row = $res->fetchArray(SQLITE3_NUM);
        $res->finalize();
        $stm->close();
        $db->close();
        return $row !== false;
    }
    function remember($username, $key, $expire) {
        $db = new \SQLite3(database, SQLITE3_OPEN_READWRITE);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        //$db->exec('PRAGMA synchronous = NORMAL');
        $stm = $db->prepare('INSERT OR REPLACE INTO remember (user, key, expire) VALUES (:user, :key, :expire)');
        $stm->bindValue(':user', $username, SQLITE3_TEXT);
        $stm->bindValue(':key', $key, SQLITE3_TEXT);
        $stm->bindValue(':expire', $expire, SQLITE3_TEXT);
        $stm->execute();
        $stm->close();
        $db->close();
    }
    function forget($username, $key) {
        $db = new \SQLite3(database, SQLITE3_OPEN_READWRITE);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        //$db->exec('PRAGMA synchronous = NORMAL');
        $stm = $db->prepare('DELETE FROM remember WHERE user = :user AND key = :key)');
        $stm->bindValue(':user', $username, SQLITE3_TEXT);
        $stm->bindValue(':key', $key, SQLITE3_TEXT);
        $stm->execute();
        $stm->close();
        $db->close();
    }
    function remembered($username, $key) {
        $db = new \SQLite3(database, SQLITE3_OPEN_READWRITE);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        //$db->exec('PRAGMA synchronous = NORMAL');
        $stm = $db->prepare('DELETE FROM remember WHERE user = :user AND key = :key AND expire > :expire');
        $stm->bindValue(':user', $username, SQLITE3_TEXT);
        $stm->bindValue(':key', $key, SQLITE3_TEXT);
        $stm->bindValue(':expire', date_format(date_create(), DATE_SQLITE), SQLITE3_TEXT);
        $stm->execute();
        $changes = $db->changes();
        $stm->close();
        $db->close();
        if ($changes > 0) return authenticate($username);
        return false;
    }
    function paid() {
        $db = new \SQLite3(database, \SQLITE3_OPEN_READONLY);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        $stm = $db->prepare('SELECT COUNT(paid) FROM users WHERE paid > :paid');
        $stm->bindValue(':paid', 1, SQLITE3_INTEGER);
        $res = $stm->execute();
        $row = $res->fetchArray(SQLITE3_NUM);
        $res->finalize();
        $stm->close();
        $db->close();
        return ($row) ? $row[0] : 0;
    }
}