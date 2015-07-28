<?php

// Default
$timestamp = time();
$host = 'api.nationalnet.com';
$method = 'GET';
$user = ''; // your myNatNet username
$api_key = ''; // api-key string found in myNatNet user profile

// The different API Endpoints
$list_graphs_path = '/api/v1/graphs';
$data_path = '/api/v1/graphs/:id/data';

/**
 * Fetch your account graphs.
 */
$uri = 'https://' . $host . $list_graphs_path;

$parameters = [
	'date' => $timestamp,
	'method' => 'GET',
	'username' => $user,
	'canonical' => get_canonical_string([]),
	'api_key' => $api_key,
	'host' => $host
];

$signature = create_signature($parameters);

$ch = curl_init();
$headers = array(
	"x-nnws-auth:$user:$signature",
	"date:".date('r', $timestamp)
);

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $uri);

$graphs = json_decode(curl_exec($ch), true);

/**
 * Fetch data for a graph
 */
$first_graph = $graphs[0];
$first_graph_id = $first_graph['id'];
$start_time = (new \DateTime("-1 month"))->getTimestamp();
$end_time = (new \DateTime("-1 month + 1 day"))->getTimestamp();

$parameters = [
	'date' => $timestamp,
	'method' => 'GET',
	'username' => $user,
	'canonical' => get_canonical_string(['start' => $start_time, 'end' => $end_time]),
	'api_key' => $api_key,
	'host' => $host
];

$signature = create_signature($parameters);
$data_path = str_replace(":id", $first_graph_id, $data_path);
$uri = 'https://' . $host . $data_path . '?start=' . $start_time . '&end=' . $end_time;

$headers = array(
	"x-nnws-auth:$user:$signature",
	"date:".date('r', $timestamp)
);

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $uri);

$data_points = json_decode(curl_exec($ch), true);

curl_close($ch);

/**
 * Build a canonical string given an array of query parameters.
 *
 * @param array $parameters
 * @return string
 */
function get_canonical_string(array $parameters) {
	ksort($parameters);

	$string = '';
	foreach ($parameters as $parameter => $value) {
		$string .= rawurlencode($parameter) . '=' . rawurlencode($value) . '&';
	}

	return $string === '' ? $string : substr($string, 0, -1);
}

/**
 * Build a HMAC request signature.
 *
 * @param array $parameters
 * @return string
 */
function create_signature(array $parameters) {
	$string_to_sign = $parameters['method'] . "\n"
		. $parameters['username'] . "\n"
		. $parameters['host'] . "\n"
		. $parameters['date'] . "\n"
		. $parameters['canonical'];

	return base64_encode(
		hash_hmac(
			'sha256',
			$string_to_sign,
			$parameters['api_key'],
			true
		)
	);
}
