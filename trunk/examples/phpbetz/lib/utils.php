<?php
function password($password) {
    return hash_hmac('sha512', $password, config::secret);
}
function login($username) {
    session();
    session_regenerate_id(true);
    $_SESSION['logged-in-username'] = $username;
}
function logoff() {
    
}
function authenticate() {
    session();
    if (isset($_SESSION['logged-in-username'])) {
        $username = $_SESSION['logged-in-username'];
        $db = new db;
        $user = $db->users->authenticate($username);
        $db->close();
        if ($user) {
            define('authenticated', true);
            define('admin', $user['admin'] === 1);
            define('username', $username);
            view::register('user', $user);
            return;
        }
    }
    define('authenticated', false);
    define('admin', false);
}
