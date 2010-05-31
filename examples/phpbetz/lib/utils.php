<?php
function password($password) {
    return hash_hmac('sha512', $password, config::secret);
}
function login($username, $remember = false) {
    @session_regenerate_id(true);
    $_SESSION['logged-in-username'] = $username;
    if ($remember) remember($username);
}
function remember($username) {
    $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown User Agent';
    $key = password(uniqid(session_id(), true) . $agent);
    $expire = date_modify(date_create(), '+40 day');
    $cookie = urlencode(sprintf('%s:%s', $username, $key));
    setcookie ('logged-in-cookie', $cookie, date_timestamp_get($expire), '/', '', false, true);
    $db = new db;
    $db->users->remember($username, $key, date_format($expire, DATE_SQLITE));
    $db->close();
}
function logoff() {
    $_SESSION = array();
    $expire = date_timestamp_get(date_modify(date_create(), '-1 day'));
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', $expire, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    if (isset($_COOKIE['logged-in-cookie'])) {
        $cookie = urldecode($_COOKIE['logged-in-cookie']);
        $parts = explode(':', $cookie);
        if (count($cookie) == 2) {
            $username = $parts[0];
            $key = $parts[1];
            $db = new db;
            $db->users->forget($username, $key);
            $db->close();
        }
        setcookie ('logged-in-cookie', '', $expire, '/', '', false, true);
    }
}
function authenticate() {
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
    if (isset($_COOKIE['logged-in-cookie'])) {
        $cookie = urldecode($_COOKIE['logged-in-cookie']);
        $parts = explode(':', $cookie);
        if (count($parts) == 2) {
            $username = $parts[0];
            $key = $parts[1];
            $db = new db;
            $user = $db->users->remembered($username, $key);
            $db->close();
            if ($user) {
                $_SESSION['logged-in-username'] = $username;
                define('authenticated', true);
                define('admin', $user['admin'] === 1);
                define('username', $username);
                view::register('user', $user);
                remember($username);
                return;
            }
        }
    }
    define('authenticated', false);
    define('admin', false);
    define('username', 'anonymous');
}
