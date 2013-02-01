<?php

namespace Ndm\JsonRpc2\Core;

/**
 * A basic class that encapsulates an erroneous response
 *
 * Also contains additional constructor methods for standard error types
 *
 */
class ResponseError implements ExportableInterface
{

    /**
     * @var int|null
     */
    public $id = null;
    /**
     * @var int
     */
    public $code;

    /**
     * @var string
     */
    public $message;

    /**
     * @var mixed|null
     */
    public $data;


    /**
     * @param int|null $id
     * @param int $code
     * @param string $message
     * @param mixed|null $data
     */
    public function __construct($id, $code, $message, $data = null)
    {
        $this->id = $id;
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function toJsonNatives()
    {
        $error = array(
            "jsonrpc" => "2.0",
            "error" => array("code" => $this->code, "message" => $this->message),
            "id" => $this->id
        );
        if (isset($this->data)) {
            $error['error']['data'] = $this->data;
        }
        return $error;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return Json::encode($this->toJsonNatives());
    }

    /**
     * @return ResponseError
     */
    public static function createInvalidRequest()
    {
        return new self(null, -32600, "Invalid Request.");
    }

    /**
     * @param mixed $id
     * @param mixed $data
     * @return ResponseError
     */
    public static function createMethodNotFound($id = null, $data = null)
    {
        return new self($id, -32601, "Method not found.", $data);
    }

    /**
     * @param mixed $id
     * @param mixed $data
     * @return ResponseError
     */
    public static function createInvalidParams($id, $data = null)
    {
        return new self($id, -32602, "Invalid params.", $data);
    }

    /**
     * @param mixed $id
     * @param mixed $data
     * @return ResponseError
     */
    public static function createInternalError($id = null, $data = null)
    {
        return new self($id, -32603, "Internal error.", $data);
    }

    /**
     * @return ResponseError
     */
    public static function createParseError()
    {
        return new self(null, -32700, "Parse error.");
    }
}
