<?php
class users extends dbo {
    function login($username, $password) {
        $sql = $this->db->prepare('SELECT COUNT(*) AS login FROM users WHERE username = ? AND password = ?');
        $sql->execute(array($username, $password));
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        return $row && (int)$row['login'] === 1;   
    }
    function register($username, $password, $email) {
        $sql = $this->db->prepare('INSERT INTO users (username, password, email, active, admin) VALUES (?, ?, ?, ?, ?)');
        return $sql->execute(array($username, $password, $email, 1, 0));
    }
    function claim($username, $claim, $email) {
        $sql = $this->db->prepare('INSERT INTO users (username, claim, email, active, admin) VALUES (?, ?, ?, ?, ?)');
        return $sql->execute(array($username, $claim, $email, 1, 0));
    }
    function claimed($claim) {
        $sql = $this->db->prepare('SELECT COUNT(*) AS claimed FROM users WHERE claim = ?');
        $sql->execute(array($claim));
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        return $row && (int)$row['claimed'] === 1;
    }
    function username_taken($username) {
        $sql = $this->db->prepare('SELECT COUNT(*) AS username FROM users WHERE username = ?');
        $sql->execute(array($username));
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        return $row && (int)$row['username'] === 1;
    }
    function email_taken($email) {
        $sql = $this->db->prepare('SELECT COUNT(*) AS email FROM users WHERE email = ?');
        $sql->execute(array($email));
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        return $row && (int)$row['email'] === 1;
    }
}