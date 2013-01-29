JSON-RPC-V2-PHP
===============

This library is a highly flexible implementation of the JSON-RPC 2.0 specification written for PHP 5.3+, adhering to the PSR standards [where applicable].

It has been designed to allow for many of the mechanisms to be changed without editing core files. It is possible to run different transports, authentication schemes, method authorisation systems, argument type validation & data sanitizing schemes, proxy system, and method list caching/parsing systems without modifying the library.

Admittedly, it's currently missing the necessary unit tests that will allow you to check the behaviour of some of your components.

If you have written any modules that could be included, or would like to discuss the library- please do!

How To Use
----------------
```php
<?php

//SKIP if already using PSR-0 compliant autoloader
// set your include path
$newIncludePath = get_include_path() . PATH_SEPARATOR . 'path/to/src';
set_include_path($newIncludePath);

require("JsonRpc\Loader.php");

\JsonRpc\Loader::register(); // adds a PSR-0 compliant loader, otherwise use classmap()

//END SKIP

// init procedure -
// 1. perform any external checks / tests on your transport layer (ie Authentication via OAuth)
// 2. initialise a transport system to obtain the rpc calls from, and return results to
// 3. get some functions to provide
// 4. register them with a dispatch system
// 5. create a server with the aforementioned dispatch & transport systems

// the transport - a simple http wrapper
$transport = new \JsonRpc\Transport\HttpTransport();


$api = new SomeClass();

//create a set of methods from the instance of SomeClass
$methods = \JsonRpc\Dispatch\ReflectionMethod::createFrom($api);
// dispatch system is responsible for invoking methods called by clients
$dispatch = new \JsonRpc\Dispatch\MapDispatch();
// register all the methods with the dispatch system
$dispatch->registerAll($methods);

// start the server
$server = new \JsonRpc\Server($transport, $dispatch);
// process the request!
$server->process();

```

Todo
-------------------

Testing:

* Unit testing for core functionality / verification that it meets required standards
* Unit testing for JsonRpc\Transport\HttpTransport
* Unit testing for JsonRpc\Dispatch\FunctionListDispatch

Implementation / Functionality:

* Example implementation of caching reflections data
* Implementation that uses Docblocks to provide type-checking, also adds hooks for parameter data checks
* Alternate transports (eg, Mail)
* Client Library
* 'Proxy' method that uses client library to invoke remote function.
* Implementation Example with OAuth Library


Structure / Work-flow
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
