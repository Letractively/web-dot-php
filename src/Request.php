<?php
/*
Class: Request

    HTTP request related functionlity.

About: Version

    $Id: Request.php 201 2007-11-27 22:33:11Z aapo.laakkonen $

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

    See also:

        <isGet>,
        <isAjax>

    Examples:

        > // checks if the request is POST
        > if (Request::isPost()) {
        >    // this is a POST request
        > } 
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

    See also:
        
        <isPost>,
        <isAjax>

    Examples:

        > // checks if the request is GET
        > if (Request::isGet()) {
        >     // this is a GET request
        > }
    */
    public static function isGet() {
        return ($_SERVER['REQUEST_METHOD'] === 'GET');
    }

    /*
    Function: isAjax

        Tells whether HTTP request is made with XMLHttpRequest  

    Returns:

        true  - if HTTP request method is made with XMLHttpRequest
        false - if HTTP request method is not made with XMLHttpRequest

    See also:

        <isPost>,
        <isGet>

    Examples:

        > // checks is the request is made with XMLHttpRequest
        > if (Request::isAjax()) {
        >    // the request is made with XMLHttpRequest
        > }
    */
    public static function isAjax() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
    }
}
