<?php
/*
Class: Request

    HTTP request related functionlity.

About: Version

    $Id$

About: License

    This file is licensed under the MIT.
*/
class Request {

    private function __construct() {}


    /*
    Function: isPost

        Tells whether HTTP request method is POST

    Returns:

        true  - if HTTP request method is POST
        false - if HTTP request method is not POST
    */
    public static function isPost() {
        return ($_SERVER['REQUEST_METHOD'] === 'POST');
    }

    /*
    Function: isGet

        Tells whether HTTP request method is GET

    Returns:

        true  - if HTTP request method is GET
        false - if HTTP request method is not GET
    */
    public static function isGet() {
        return ($_SERVER['REQUEST_METHOD'] === 'GET');
    }

    /*
    Function: isAjax

        Tells whether HTTP request is madi with XMLHttpRequest  

    Returns:

        true  - if HTTP request method is made with XMLHttpRequest
        false - if HTTP request method is not made with XMLHttpRequest
    */
    public static function isAjax() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
    }
}