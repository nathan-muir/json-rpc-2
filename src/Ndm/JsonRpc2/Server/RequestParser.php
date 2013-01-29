<?php
namespace Ndm\JsonRpc2\Server;

use Ndm\JsonRpc2\Request;
use \Ndm\JsonRpc2\Json;

class RequestParser
{


    /**
     * @const int Limits the depth parsed by JSON requests
     */
    const JSON_DECODE_DEPTH_LIMIT = 24;

    /**
     * @var int
     */
    private $jsonDecodeDepthLimit;

    /**
     * @param int $jsonDecodeDepthLimit
     */
    public function __construct($jsonDecodeDepthLimit = self::JSON_DECODE_DEPTH_LIMIT)
    {
        $this->jsonDecodeDepthLimit = $jsonDecodeDepthLimit;
    }

    /**
     * @author Matt Morley (MPCM)
     * @param mixed $request
     * @return bool
     */
    private function isValidRequest($request)
    {
        // per the 2.0 specification
        // a request object must:
        // be an object
        if (!is_object($request)) {
            return false;
        }

        // contain a jsonrpc member that is a string
        if (!isset($request->jsonrpc) || strcmp($request->jsonrpc, '2.0') !== 0) {
            return false;
        }

        // contain a method member that is a string
        if (!isset($request->method) || !is_string($request->method) || strlen($request->method) === 0) {
            return false;
        }

        // if it contains a params member
        //    that member must be an array or an object
        if (property_exists($request, 'params') && !is_array($request->params) && !is_object($request->params)) {
            return false;
        }

        // if it contains an id member
        //    that member must be a string, number, or null
        if (property_exists($request, 'id') && !is_null($request->id) && !is_int($request->id) && !is_float(
            $request->id
        ) && !is_string($request->id)
        ) {
            return false;
        }

        // it passes the tests
        return true;
    }

    /**
     * @param $request
     * @return \Ndm\JsonRpc2\Request|null
     */
    private function createFrom($request)
    {
        if (!$this->isValidRequest($request)) {
            return null;
        }
        // import the id, method and params from the object
        return new Request(
            $request->method,
            isset($request->params) ? $request->params : null,
            property_exists($request, 'id'),
            isset($request->id) ? $request->id : null
        );
    }

    /**
     * @param string $json
     *
     * @throws \Ndm\JsonRpc2\Exception_ParseError
     * @throws \Ndm\JsonRpc2\Exception_InvalidRequest
     *
     * @return Request|Request[]
     */
    public function parse($json)
    {
        // decode the string
        $request = Json::decode($json, $this->jsonDecodeDepthLimit);

        // create a new collection
        if (is_array($request) && count($request) > 0) {
            // non-empty arrays are attempts at batch requests
            $collection = array();
            foreach ($request as $singleRequest) {
                $collection[] = $this->createFrom($singleRequest);
            }
            return $collection;
        } else {
            // all other valid json is treated as a single request
            return $this->createFrom($request);
        }
    }
}