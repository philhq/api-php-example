<?php
require __DIR__ . "/vendor/autoload.php";

$api = new NationalNet\API('username', 'API key');
$graphs = $api->graphs();

print_r($graphs);
