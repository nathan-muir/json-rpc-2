json-rpc-2
===============

This library is a highly flexible implementation of the JSON-RPC 2.0 specification written for PHP 5.3+, adhering to the PSR standards [where applicable].

It has been designed to allow for many of the mechanisms to be changed without editing core files. It is possible to run different transports, authentication schemes, method authorisation systems, argument type validation & data sanitizing schemes, proxy system, and method list caching/parsing systems without modifying the library.

Admittedly, it's currently missing the necessary unit tests that will allow you to check the behaviour of some of your components.

If you have written any modules that could be included, or would like to discuss the library- please do!

How to Use Client - Basic
---------------------
```php
<?php
namespace MyCompany\Package;

use \Ndm\JsonRpc2\Client\HttpClient;
// use vendor autoload from composer
require('vendor/autoload.php');

// create a Client using the HttpTransport layer
$client = HttpClient::connect('http://api.somesite.com/');
// call a method, using named parameters
$client->call('somemethod', array('abc'=>123));

// alternatively, use the "native" interface
$nativeClient = $client->getNativeClient();
// however calls must use positional parameters
$nativeClient->somemethod(123);


```

How To Use Server - Basic
----------------
```php
<?php


namespace MyCompany\Package;

use Ndm\JsonRpc2\HttpServer;
use \Ndm\JsonRpc2\Server\Exception\TransportReceiveException;
use \Ndm\JsonRpc2\Server\Exception\TransportReplyException;

require ('vendor/autoload.php'); // require autoloader created by composer

$api = new SomeClass();
$methods =  array (
    'static_func'=> 'AnotherStatic::Func',
    'global_func' => 'do_abc',
    'some_func' => function($p) { return $p + 1; }
);
// register the server with a set of methods, either from an instance, a class with static methods, or as a map of 'callables'
$server = HttpServer::register( $api, 'StaticClass',$methods);

// process the request!
try {
    $server->process();
} catch (TransportReceiveException $treceive){
    // exceptions on this layer - like not using HTTP-POST
    header('HTTP/1.0 400 Bad Request');
    exit;
} catch (TransportReplyException $treply){
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}

```

How To Use Server - Advanced
----------------
```php
<?php


namespace MyCompany\Package;

use \Ndm\JsonRpc2\Server\Server;
use \Ndm\JsonRpc2\Server\Exception\TransportReceiveException;
use \Ndm\JsonRpc2\Server\Exception\TransportReplyException;
use \Ndm\JsonRpc2\Server\Transport\HttpTransport;
use \Ndm\JsonRpc2\Server\Dispatch as Dispatch;

require ('vendor/autoload.php'); // require autoloader created by composer

// init procedure -
// 1. perform any external checks / tests on your transport layer (ie Authentication via OAuth)
// 2. initialise a transport system to obtain the rpc calls from, and return results to
// 3. get some functions to provide
// 4. register them with a dispatch system
// 5. create a server with the aforementioned dispatch & transport systems

// the transport - a simple http wrapper
$transport = new HttpTransport(HttpTransport::OPT_REQUIRE_HTTPS | HttpTransport::OPT_SEND_OUTPUT_HEADERS);


$api = new SomeClass();

//create a set of methods from the instance of SomeClass
$methods = Dispatch\ReflectionMethod::createFrom($api);
// dispatch system is responsible for invoking methods called by clients
$dispatch = new Dispatch\MapDispatch();
// register all the methods with the dispatch system
$dispatch->registerAll($methods);

// start the server
$server = new Server($transport, $dispatch);
// process the request!
try {
    $server->process();
} catch (TransportReceiveException $treceive){
    header('HTTP/1.0 400 Bad Request');
    exit;
} catch (TransportReplyException $treply){
     header('HTTP/1.0 500 Internal Server Error');
     exit;
}

```

Todo
-------------------

Documentation:

* Client Documentation

Testing:

* Unit Test

    * Core\ResponseParser
    * Server\Server [mock the 'receive' function, or wrap the combination of TransportInterface & RequestParser]
    * Client\Client [mock the 'send' function, or wrap the combination of TransportInterface & ResponseParser]
    * Client\BatchClient
    * Server\Transport\HttpTransport
    * Un-implemented Classes / Transports

Implementation / Functionality:

* OAuth & Basic Auth Client Wrapper using HttpTransport
* More comprehensive Dispatch system implementations (Caching, Docblock Parsing, Type Checking)


Server Structure / Work-flow
-------------------

Transport Lifecycle:

1. Reads the transport layer / source - provides string/text only
2. Receives a text reply to render

Server Lifecycle:

1. Obtains Requests text from Transport via 'receive'.
2. Uses \JsonRpc\RequestParser->parse() to parse in to objects
3. Iterates through each requests, obtaining the result through Dispatch->invoke. Exceptions are caught and turned into ResponseError.
4. Passes the result, back to the Transport to be rendered.

Dispatch Lifecycle:

1. Is passed a method-alias and arguments, and must return a result or throw an exception.
