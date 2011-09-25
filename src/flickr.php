<?php
// Flickr
// TODO: POSTing to Flickr not implemented.
function flickr($args) {
	$endpoint = isset($args['endpoint']) ? $args['endpoint'] : 'http://api.flickr.com/services/rest/';
	$secret = isset($args['api_secret']) ? $args['api_secret'] : null;
	unset($args['endpoint'], $args['api_secret']);
	if ($secret != null) {
		ksort($args);
		$api_sig = $secret;
		foreach($args as $k => $v) $api_sig .= $k . $v;
		$api_sig = md5($api_sig);
		$args['api_sig'] = $api_sig;
	}
	$url = $endpoint . '?' . http_build_query($args);
	if (substr($endpoint, -15) === '/services/auth/') return $url;
	$response = file_get_contents($url);
	return isset($args['format']) && $args['format'] === 'php_serial' ? unserialize($response) : $response;
}