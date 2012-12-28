JSON-RPC-V2-PHP
===============

This is a work in progress. The library may be functional, however a few things are still required.


Testing:

* Unit testing for core functionality / verification that it meets required standards
* Unit testing for JSON_RPC\Transport\HttpTransport
* Unit testing for JSON_RPC\Dispatch\FunctionListDispatch

Implementation / Functionality:

* Dispatch system which parses class definitions / doc-blocks to produce API
* Interface for Dispatch system that performs authorisation on methods (allows implementations to provide their own authorisation scheme)
* Alternate transports
* Client Library
* Implementation Example with OAuth Library