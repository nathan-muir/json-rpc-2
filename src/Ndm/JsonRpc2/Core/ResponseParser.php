<?php

namespace Ndm\JsonRpc2\Core;

/**
 *
 */
class ResponseParser
{

    /**
     * @const int Limits the depth parsed by JSON response
     */
    const JSON_DECODE_DEPTH_LIMIT = 512;

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
     * @param $response
     *
     * @throws Exception\InvalidResponseException
     *
     * @return Response|ResponseError|null
     */
    private function createFrom($response)
    {
        // response must be an object
        if (!is_object($response)) {
            throw new Exception\InvalidResponseException("The response was not an object.");
        }

        // contain a jsonrpc member that is a string
        if (!isset($response->jsonrpc) || strcmp($response->jsonrpc, '2.0') !== 0) {
            throw new Exception\InvalidResponseException("The response did not specify or had an expected value for required attribute 'jsonrpc'");
        }
        // must contain an ID - and it must be null, integer, float, or string
        if (!property_exists($response, 'id') || (!is_null($response->id) && !is_int($response->id) && !is_float(
            $response->id
        ) && !is_string($response->id))
        ) {
            throw new Exception\InvalidResponseException("The response did not specify, or had an unexpected value for required attribute 'id'");
        }
        // detect whether response is a result or an error
        $hasResult = property_exists($response, 'result');
        $hasError = property_exists($response, 'error');
        // if missing, or containing both result and error - throw relevant exceptions
        if ($hasResult && $hasError) {
            throw new Exception\InvalidResponseException("The response contains both 'result' and 'error' attributes. The response must contain only one of these attributes.");
        } elseif (!$hasResult && !$hasError) {
            throw new Exception\InvalidResponseException("The response did not contain either 'result' or 'error' attributes");
        } elseif ($hasResult) {
            // create a standard response using id & result
            return new Response($response->id, $response->result);
        } else {
            // the response error attribute must contain members "message" & "code"
            if (!property_exists($response->error, 'code') || !property_exists($response->error, 'message')) {
                throw new Exception\InvalidResponseException("The response error attribute does not contain required attributes 'code' and 'message'");
            }
            // the response error may contain a data-member
            $data = null;
            if (property_exists($response->error, 'data')) {
                $data = $response->error->data;
            }
            // create a new error response
            return new ResponseError($response->id, $response->error->code, $response->error->message, $data);
        }
    }

    /**
     * @param $json
     *
     * @throws Exception\JsonParseException
     * @throws Exception\InvalidResponseException
     *
     * @return BatchResponse|Response|ResponseError|null
     */
    public function parse($json)
    {
        // if there's no data- return null
        if ($json === '') {
            return null;
        }

        // decode the string
        $response = Json::decode($json, $this->jsonDecodeDepthLimit);

        // create a new collection
        if (is_array($response) && count($response) > 0) {
            // non-empty arrays are attempts at batch requests
            $collection = array();
            foreach ($response as $singleResponse) {
                $collection[] = $this->createFrom($singleResponse);
            }
            return new BatchResponse($collection);
        } else {
            // all other valid json is treated as a single request
            return $this->createFrom($response);
        }
    }
}
