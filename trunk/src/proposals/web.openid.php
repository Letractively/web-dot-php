<?php
namespace {
    function openid_discover($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/xrds+xml'));
        $resp = curl_exec($ch);
        curl_close($ch);
        return simplexml_load_string($resp);
    }
    function openid_authenticate($url, array $params = array()) {
        $defaults = array(
            'openid.mode' => 'checkid_setup',
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
            'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
            'openid.identity' => 'http://specs.openid.net/auth/2.0/identifier_select'
        );
        $params = array_merge($defaults, $params);
        $qs = parse_url($url, PHP_URL_QUERY);
        $url .= isset($qs) ? '&' : '?';
        $url .= http_build_query($params);
        redirect($url);
    }
    function openid_check() {
        $url = $_GET['openid_op_endpoint'];
        $data = str_replace('openid.mode=id_res', 'openid.mode=check_authentication', $_SERVER['QUERY_STRING']);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $resp = curl_exec($ch);
        curl_close($ch);
        return strpos($resp, 'is_valid:true') === 0;
    }
}