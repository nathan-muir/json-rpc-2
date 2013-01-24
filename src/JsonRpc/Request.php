<?php

namespace JsonRpc;

/**
 * An object representation of a single request
 *
 * @author Matt Morley (MPCM)
 * @author Nathan Muir
 * @version 2012-12-24
 *
 */
class Request
{
    /**
     * @const int Limits the depth parsed by JSON requests
     */
    const REQUEST_JSON_DEPTH_LIMIT = 24;

    /**
     * @var string|int|null
     */
    public $id = null;

    /**
     * @var bool
     */
    public $hasId = false;
    /**
     * @var array|null
     */
    public $params = null;

    /**
     * @var string
     */
    public $method;

    /**
     * @param string $method
     * @param array|\stdClass $params
     * @param bool $hasId As the $id can be null, this is to indicate whether the request has an id
     * @param int|null|string|float $id
     */
    public function __construct($method, $params = null, $hasId = false, $id = null)
    {
        $this->method = $method;
        $this->params = $params;
        if ($hasId) {
            $this->hasId = true;
            $this->id = $id;
        }
    }

    /**
     * @return bool
     */
    public function isNotification()
    {
        return !$this->hasId;
    }

    /**
     * @return array
     */
    public function render()
    {
        $render = array(
            "jsonrpc" => "2.0",
            "method" => $this->method
        );
        if (isset($this->params)) {
            $render['params'] = $this->params;
        }
        if ($this->hasId) {
            $render['id'] = $this->id;
        }
        return $render;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->render());
    }

    /**
     * @author Matt Morley (MPCM)
     * @param mixed $request
     * @return bool
     */
    private static function isValidRequest($request)
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
     * @return Request|null
     */
    private static function createFrom($request)
    {
        if (!self::isValidRequest($request)) {
            return null;
        }
        // import the id, method and params from the object
        return new self($request->method, isset($request->params) ? $request->params : null, property_exists(
            $request,
            'id'
        ), isset($request->id) ? $request->id : null);
    }

    /**
     * @param string $data
     *
     * @throws \JsonRpc\Exception_ParseError
     * @throws \JsonRpc\Exception_InvalidRequest
     *
     * @return Request|Request[]
     */
    public static function createFromString($data)
    {
        // decode the string
        $request = json_decode($data, false, self::REQUEST_JSON_DEPTH_LIMIT);
        // obtain any errors
        $error = json_last_error();

        if ($error != JSON_ERROR_NONE) {
            // in case of error, return useful message why it failed to parse
            // from http://php.net/manual/en/function.json-last-error.php
            switch ($error) {
                case JSON_ERROR_DEPTH:
                    $error_text = 'Maximum request nesting depth reached';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error_text = 'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error_text = 'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $error_text = 'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $error_text = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $error_text = 'Unknown error';
                    break;
            }
            // throw Parse Error
            //TODO decide whether to show json-parser error data or not
            throw new Exception_ParseError( /* $error_text */);
        }

        // create a new collection
        if (is_array($request) && count($request) > 0) {
            // non-empty arrays are attempts at batch requests
            $collection = array();
            foreach ($request as $singleRequest) {
                $collection[] = self::createFrom($singleRequest);
            }
            return $collection;
        } else {
            // all other valid json is treated as a single request
            return self::createFrom($request);
        }
    }

}