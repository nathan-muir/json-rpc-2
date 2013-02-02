<?php

namespace MyCompany\Package;

use \Ndm\JsonRpc2\Client\HttpClient;

use \Ndm\OAuth\Consumer;
use \Ndm\OAuth\SignatureMethod\Hmac as SignatureMethodHmac;

require('../vendor/autoload.php');

$consumer = new Consumer('your-key','your-secret', new SignatureMethodHmac());

// create a Client using the HttpTransport layer
$client = HttpClient::connectOAuth('http://api.yoursite.com', $consumer);
// call a method, using named parameters
$client->call('some.method');
