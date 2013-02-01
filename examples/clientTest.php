<?php

namespace MyCompany\Package;

use \Ndm\JsonRpc2\Client\HttpClient;

require('../vendor/autoload.php');

// create a Client using the HttpTransport layer
$client = HttpClient::connect('http://api.somesite.com/');
// call a method, using named parameters
$client->call('somemethod', array('abc'=>123));

// alternatively, use the "native" interface
$nativeClient = $client->getNativeClient();
// however calls must use positional parameters
$nativeClient->somemethod(123);
