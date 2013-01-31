JSON-RPC-V2-PHP
===============

This library is a highly flexible implementation of the JSON-RPC 2.0 specification written for PHP 5.3+, adhering to the PSR standards [where applicable].

It has been designed to allow for many of the mechanisms to be changed without editing core files. It is possible to run different transports, authentication schemes, method authorisation systems, argument type validation & data sanitizing schemes, proxy system, and method list caching/parsing systems without modifying the library.

Admittedly, it's currently missing the necessary unit tests that will allow you to check the behaviour of some of your components.

If you have written any modules that could be included, or would like to discuss the library- please do!

How To Use [Server]
----------------
```php
<?php


namespace MyCompany\Package;

use Ndm\JsonRpc2\Server as Server;

require ('vendor/autoload.php'); // require autoloader created by composer

// init procedure -
// 1. perform any external checks / tests on your transport layer (ie Authentication via OAuth)
// 2. initialise a transport system to obtain the rpc calls from, and return results to
// 3. get some functions to provide
// 4. register them with a dispatch system
// 5. create a server with the aforementioned dispatch & transport systems

// the transport - a simple http wrapper
$transport = new Server\Transport\HttpTransport();


$api = new SomeClass();

//create a set of methods from the instance of SomeClass
$methods = Server\Dispatch\ReflectionMethod::createFrom($api);
// dispatch system is responsible for invoking methods called by clients
$dispatch = new Server\Dispatch\MapDispatch();
// register all the methods with the dispatch system
$dispatch->registerAll($methods);

// start the server
$server = new Server\Server($transport, $dispatch);
// process the request!
try {
    $server->process();
} catch (Server\Exception\TransportReceiveException $treceive){
    header('HTTP/1.0 400 Bad Request');
    exit;
} catch (Server\Exception\TransportReplyException $treply){
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

* Client HttpTransport using 'stream_context_create'
* OAuth & Basic Auth Client Wrapper using HttpTransport
* Client\Client binding using __call
* 'Shortcut' methods for Client & Server using inbuilt transports, eg. $client = HttpClient::connect('http://example.com/jsonrpc');, $server = HttpServer::register($object, 'Class', $methodMap);
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
