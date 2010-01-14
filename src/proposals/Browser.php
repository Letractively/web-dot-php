<?php
/**
$Id$

Class: Browser

    Browser related functionality.

About: Version

    $Revision$

About: Author

    $Author$

About: License

    This file is licensed under the MIT.
 */
class Browser {

    private function __construct() {}

    /**
    Function: isMobile

        Tells whether HTTP request is made with a mobile browser

    Returns:

        true  - if HTTP request method is made with a mobile browser
        false - if HTTP request method is not made with a mobile browser

    See also:

        <isBot>

    Examples:

        > if (Browser::isMobile()) {
        >    // render content for a mobile browser
        > }
     */
    public static function isMobile() {
        
        $op = null;
        $ac = null;
        
        if (array_key_exists('HTTP_X_OPERAMINI_PHONE', $_SERVER)) {
            $op = strtolower($_SERVER['HTTP_X_OPERAMINI_PHONE']);
        }
        
        if (array_key_exists('HTTP_ACCEPT', $_SERVER)) {
            $ac = strtolower($_SERVER['HTTP_ACCEPT']);
        }
        
        if (strpos($ac, 'application/vnd.wap.xhtml+xml') !== false || $op !== null) {
            return true;
        } else {
            
            $mobiles = array('sony', 'symbian', 'nokia', 'samsung', 'mobile', 'windows ce', 'epoc', 'opera mini', 'nitro', 'j2me', 'midp-', 'cldc-', 'netfront', 'mot', 'up.browser', 'up.link', 'audiovox', 'blackberry', 'ericsson', 'panasonic', 'philips', 'sanyo', 'sharp', 'sie-', 'portalmmm', 'blazer', 'avantgo', 'danger', 'palm', 'series60', 'palmsource', 'pocketpc', 'smartphone', 'rover', 'ipaq', 'au-mic', 'alcatel', 'ericy', 'vodafone', 'wap1.', 'wap2.');
            
            $browser = strtolower($_SERVER['HTTP_USER_AGENT']);
            
            foreach ($mobiles as $mobile) {
                
                if (strpos($browser, $mobile) !== false) {
                    return true;
                }
            }
            
            return false;
        }
    }

    /**
    Function: isBot

        Tells whether HTTP request is made by a known bot or a search engine

    Returns:

        true  - if HTTP request method is made by a known bot or search engine
        false - if HTTP request method is not made by a known bot or search engine

    See also:

        <isMobile>

    Examples:

        > if (Browser::isBot()) {
        >    // a bot or a search engine was detected
        > }
     */
    public static function isBot() {
        
        if (array_key_exists('REMOTE_ADDR', $_SERVER) && $_SERVER['REMOTE_ADDR'] === '66.249.65.39') {
            return true;
        } else {
            
            $bots = array('googlebot', 'mediapartners', 'yahooysmcm', 'baiduspider', 'msnbot', 'slurp', 'ask', 'teoma', 'spider', 'heritrix', 'attentio', 'twiceler', 'irlbot', 'fast crawler', 'fastmobilecrawl', 'jumpbot', 'googlebot-mobile', 'yahooseeker', 'motionbot', 'mediobot', 'chtml generic', 'nokia6230i/. fast crawler');
            
            $browser = strtolower($_SERVER['HTTP_USER_AGENT']);
            
            foreach ($bots as $bot) {
                
                if (strpos($browser, $bot) !== false) {
                    return true;
                }
            }
            
            return false;
        }
    }
}
