<?php

class Request {

    /* =======================================================================
     * Constructors
     * ======================================================================= */

    private function __construct() {}

    /* =======================================================================
     * Methods
     * ======================================================================= */

    public static function isPost() {
        return ($_SERVER['REQUEST_METHOD'] === 'POST');
    }

    public static function isGet() {
        return ($_SERVER['REQUEST_METHOD'] === 'GET');
    }

    public static function isAjax() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
    }
}