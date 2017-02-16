<?php

require 'apps/maarch_entreprise/tools/elasticsearch-php/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$hosts = [
    '192.168.21.15:9200',         // IP + Port
    // '192.168.1.2',              // Just IP
    // 'mydomain.server.com:9201', // Domain + Port
    // 'mydomain2.server.com',     // Just Domain
    // 'https://localhost',        // SSL to localhost
    // 'https://192.168.1.3:9200'  // SSL to IP + Port
];
$clientBuilder = ClientBuilder::create();   // Instantiate a new ClientBuilder
$clientBuilder->setHosts($hosts);           // Set the hosts
$client = $clientBuilder->build();          // Build the client object

//$client = ClientBuilder::create()->build();

echo '<pre>';
print_r($client);
echo '</pre>';
exit;


$params = [
    'index' => 'my_index',
    'type' => 'my_type',
    'id' => 'my_id'
];

$response = $client->get($params);
print_r($response);
