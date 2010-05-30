<?php
function password($password) {
    return hash_hmac('sha512', $password, config::secret);
}
function login($username) {
    session();
    session_regenerate_id(true);
    $_SESSION['logged-in-username'] = strval($username);
}
function logoff() {
    
}
function authenticate() {
    session();
    if (isset($_SESSION['logged-in-username'])) {
        $username = $_SESSION['logged-in-username'];
        $db = new db;
        $user = $db->users->authenticate($username);
        $db = null;
        if ($user) {
            define('authenticated', true);
            define('admin', (int)$user['admin'] === 1);
            view::user('user', $user);
        } else {
            define('authenticated', false);
            define('admin', false);
        }
    } else {
        define('authenticated', false);
        define('admin', false);
    }
}
