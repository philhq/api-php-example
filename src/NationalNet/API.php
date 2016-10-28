<?php
namespace NationalNet;

use RuntimeException;

class API
{
  private $timestamp = null;
  private $host = null;
  private $method = null;
  private $user = null;
  private $apiKey = null;
  private $conn = null;

  public function __construct($user, $api_key)
  {
    $this->timestamp = time();
    $this->host = 'api.nationalnet.com';
    $this->method = 'GET';
    $this->user = $user;
    $this->apiKey = $api_key;
    $this->conn = curl_init();
    curl_setopt($this->conn, CURLOPT_RETURNTRANSFER, 1);
  }

  public function __destruct()
  {
    curl_close($this->conn);
  }

  public function graphs()
  {
    return $this->api('/api/v1/graphs');
  }

  private function api($endpoint)
  {
    $uri = 'https://' . $this->host . $endpoint;
    curl_setopt($this->conn, CURLOPT_URL, $uri);

    // Generate sig
    $parameters = [
      'date' => $this->timestamp,
      'method' => 'GET',
      'username' => $this->user,
      'canonical' => $this->get_canonical_string([]),
      'api_key' => $this->apiKey,
      'host' => $this->host
    ];
    $signature = $this->sign($parameters);

    // header
    $headers = array(
      "x-nnws-auth:{$this->user}:{$signature}",
      "date:". date('r', $this->timestamp)
    );
    curl_setopt($this->conn, CURLOPT_HTTPHEADER, $headers);

    // Execute request
    $response = curl_exec($this->conn);
    if (!$response) {
      throw new RuntimeException("[". curl_errno($this->conn) ."]" . curl_error($this->conn));
    }

    $data = json_decode($response, true);
    if ($data === null) {
      throw new RuntimeException("Unable to decode JSON data from response: {$response}");
    }

    return $data;
  }

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
  private function sign(array $parameters)
  {
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
}
