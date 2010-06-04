<?php
function password($password) {
    return hash_hmac('sha512', $password, secret);
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
    db\users\remember($username, $key, date_format($expire, DATE_SQLITE));
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
            db\users\forget($username, $key);
        }
        setcookie ('logged-in-cookie', '', $expire, '/', '', false, true);
    }
}
function authenticate() {
    if (isset($_SESSION['logged-in-username'])) {
        $username = $_SESSION['logged-in-username'];
        $user = db\users\authenticate($username);
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
            $user = db\users\remembered($username, $key);
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
function portlets() {
    view::register('single', db\bets\single(username));
    view::register('upcoming', db\bets\games(username, 4));
}
function weekday($date, $length = null) {
    $weekdaynum = (int)date_format($date, 'w');
    switch ($weekdaynum) {
        case 0: $date = 'Sunnuntai'; break;
        case 1: $date = 'Maanantai'; break;
        case 2: $date = 'Tiistai'; break;
        case 3: $date = 'Keskiviikko'; break;
        case 4: $date = 'Torstai'; break;
        case 5: $date = 'Perjantai'; break;
        case 6: $date = 'Lauantai'; break;
    }
    if ($length == null) return $date;
    return substr($date, 0, $length);
}