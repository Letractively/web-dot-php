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
    define('started', db\bets\started());
    if (isset($_SESSION['logged-in-username'])) {
        $username = $_SESSION['logged-in-username'];
        $user = db\users\authenticate($username);
        if ($user) {
            define('authenticated', true);
            define('admin', $user['admin'] === 1);
            define('username', $username);
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
    view::register('user', db\bets\single(username));
    view::register('upcoming', db\bets\games(username, 2));
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

function cache_store($key, $var, $ttl = 0) {
    if (!function_exists('apc_store')) return false;
    return apc_store($key, $var, $ttl);
}

function cache_fetch($key) {
    if (!function_exists('apc_fetch')) return false;
    return apc_fetch($key);
}

function cache_delete($key) {
    if (!function_exists('apc_delete')) return false;
    return apc_delete($key);
}

function smileys_array() {
    $smileys = array();
    $smileys[] = array('src' => 'grin.gif', 'title' => 'grin', 'keys' => array(':-)'));
    $smileys[] = array('src' => 'lol.gif', 'title' => 'lol', 'keys' => array(':D', ':-D', ':lol:'));
    $smileys[] = array('src' => 'cheese.gif', 'title' => 'cheese', 'keys' => array(':cheese:'));
    $smileys[] = array('src' => 'smile.gif', 'title' => 'smile', 'keys' => array(':)'));
    $smileys[] = array('src' => 'wink.gif', 'title' => 'wink', 'keys' => array(';-)', ';)'));
    $smileys[] = array('src' => 'smirk.gif', 'title' => 'smirk', 'keys' => array(':smirk:'));
    $smileys[] = array('src' => 'rolleyes.gif', 'title' => 'rolleyes', 'keys' => array(':roll:'));
    $smileys[] = array('src' => 'confused.gif', 'title' => 'confused', 'keys' => array(':-S'));
    $smileys[] = array('src' => 'surprise.gif', 'title' => 'surprised', 'keys' => array(':wow:'));
    $smileys[] = array('src' => 'bigsurprise.gif', 'title' => 'big surprise', 'keys' => array(':bug:'));
    $smileys[] = array('src' => 'tongue_laugh.gif', 'title' => 'tongue laugh', 'keys' => array(':-P'));
    $smileys[] = array('src' => 'tongue_rolleye.gif', 'title' => 'tongue rolleye', 'keys' => array('%-P', '%P'));
    $smileys[] = array('src' => 'tongue_wink.gif', 'title' => 'tongue wink', 'keys' => array(';-P'));
    $smileys[] = array('src' => 'rasberry.gif', 'title' => 'raspberry', 'keys' => array(':P'));
    $smileys[] = array('src' => 'blank.gif', 'title' => 'blank stare', 'keys' => array(':blank:', ':long:'));
    $smileys[] = array('src' => 'mad.gif', 'title' => 'mad', 'keys' => array('>:(', ':mad:'));
    $smileys[] = array('src' => 'angry.gif', 'title' => 'angry', 'keys' => array(':angry:', '>:-('));
    $smileys[] = array('src' => 'beer.gif', 'title' => 'beer', 'keys' => array('(b)', '(B)'));
    $smileys[] = array('src' => 'question.gif', 'title' => 'question', 'keys' => array(':question:'));
    $smileys[] = array('src' => 'soccer.gif', 'title' => 'soccer ball', 'keys' => array('(so)'));
    $smileys[] = array('src' => 'shock.gif', 'title' => 'shock', 'keys' => array(':ahhh:', ':shock:'));
    $smileys[] = array('src' => 'ohh.gif', 'title' => 'ohh', 'keys' => array(':ohh:'));
    $smileys[] = array('src' => 'grrr.gif', 'title' => 'grrr', 'keys' => array(':grrr:'));
    $smileys[] = array('src' => 'ohoh.gif', 'title' => 'ohoh', 'keys' => array('8-/'));
    $smileys[] = array('src' => 'gulp.gif', 'title' => 'gulp', 'keys' => array(':gulp:'));
    $smileys[] = array('src' => 'downer.gif', 'title' => 'downer', 'keys' => array(':down:'));
    $smileys[] = array('src' => 'embarrassed.gif', 'title' => 'red face', 'keys' => array(':red:'));
    $smileys[] = array('src' => 'sick.gif', 'title' => 'sick', 'keys' => array(':sick:'));
    $smileys[] = array('src' => 'shuteye.gif', 'title' => 'shut eye', 'keys' => array(':shut:'));
    $smileys[] = array('src' => 'hmm.gif', 'title' => 'hmmm', 'keys' => array(':-/'));
    $smileys[] = array('src' => 'zip.gif', 'title' => 'zipper', 'keys' => array(':zip:'));
    $smileys[] = array('src' => 'kiss.gif', 'title' => 'kiss', 'keys' => array(':kiss:'));
    $smileys[] = array('src' => 'shade_smile.gif', 'title' => 'cool smile', 'keys' => array(':coolsmile:'));
    $smileys[] = array('src' => 'shade_smirk.gif', 'title' => 'cool smirk', 'keys' => array(':coolsmirk:'));
    $smileys[] = array('src' => 'shade_grin.gif', 'title' => 'cool grin', 'keys' => array(':coolgrin:'));
    $smileys[] = array('src' => 'shade_hmm.gif', 'title' => 'cool hmm', 'keys' => array(':coolhmm:'));
    $smileys[] = array('src' => 'shade_mad.gif', 'title' => 'cool mad', 'keys' => array(':coolmad:'));
    $smileys[] = array('src' => 'shade_cheese.gif', 'title' => 'cool mad', 'keys' => array(':coolcheese:'));
    $smileys[] = array('src' => 'vampire.gif', 'title' => 'vampire', 'keys' => array(':vampire:'));
    $smileys[] = array('src' => 'snake.gif', 'title' => 'snake', 'keys' => array(':snake:'));
    $smileys[] = array('src' => 'exclaim.gif', 'title' => 'exclaim', 'keys' => array(':exclaim:'));
    $smileys[] = array('src' => 'thumbs_down.gif', 'title' => 'thumbs down', 'keys' => array('(n)'));
    $smileys[] = array('src' => 'thumbs.gif', 'title' => 'thumb', 'keys' => array('(y)', '(Y)'));
    $smileys[] = array('src' => 'finger.gif', 'title' => 'finger', 'keys' => array(':finger:'));

    return $smileys;
}