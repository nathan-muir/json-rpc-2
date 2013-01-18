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
     * @var bool
     */
    public $valid = false;
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var array
     */
    public $params = array();

    /**
     * @var string|null
     */
    public $method;


    /**
     * @param mixed $data
     */
    public function __construct($data)
    {
        // check the validity of the request
        if ($this->isValidRequest($data)) {
            $this->valid = true;

            // import the id, method and params from the object
            if (isset($data->id)) {
                $this->id = $data->id;
            }
            $this->method = $data->method;
            if (isset($data->params)) {
                $this->params = $data->params;
            }
        }
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
        if (!isset($request->method) || !is_string($request->method)) {
            return false;
        }

        // if it contains a params member
        //    that member must be an array or an object
        if (isset($request->params) && !is_array($request->params) && !is_object($request->params)) {
            return false;
        }

        // if it contains an id member
        //    that member must be a string, number, or null
        if (isset($request->id) && !is_string($request->id) && !is_numeric($request->id)) {
            return false;
        }

        // it passes the tests
        return true;
    }

    /**
     * @return bool
     */
    public function isNotification()
    {
        return !isset($this->id);
    }


    /**
     * @param array $data
     * @return Request
     */
    public static function createFromAssoc($data)
    {
        $request = (object)array('method' => null, 'params' => array(), 'id' => null, 'jsonrpc' => '2.0');

        if (isset($data['method'])) {
            $request->method = $data['method'];
        }

        if (isset($data['id'])) {
            $request->id = $data['id'];
        }

        if (isset($data['params'])) {
            $params = $data['params'];
            // if it's an hash, and it's not an "array", convert to object
            if (is_array($params) && array_keys($params) !== range(0, count($params) - 1)) {
                $request->params = (object)$params;
            } else {
                $request->params = $params;
            }
        }
        return new self($request);
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
        $json_data = json_decode($data, false, self::REQUEST_JSON_DEPTH_LIMIT);
        // obtain any errors
        $error = json_last_error();

        if ($error == JSON_ERROR_NONE) {
            // create the request/requests from the decoded data
            return self::createFrom($json_data);
        }

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

    /**
     * @param mixed $mixed
     *
     * @throws \JsonRpc\Exception_InvalidRequest
     *
     * @return Request|Request[]
     */
    public static function createFrom($mixed)
    {
        // create a new collection
        if (is_object($mixed)) { // && get_class($mixed) == 'StdClass'
            // objects are assumed to be attempts at single requests
            return new self($mixed);
        } elseif (is_array($mixed) && count($mixed) > 0) {
            // non-empty arrays are attempts at batch requests
            $collection = array();
            foreach ($mixed as $request) {
                $collection[] = new self($request);
            }
            return $collection;
        } else {
            // everything else is invalid
            throw new Exception_InvalidRequest();
        }
    }

}