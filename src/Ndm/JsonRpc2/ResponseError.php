<?php

namespace Ndm\JsonRpc2;

/**
 * A basic class that encapsulates an erroneous response
 *
 * Also contains additional constructor methods for standard error types
 *
 * @author Nathan Muir
 * @version 2012-12-24
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
     * @param int|null $id
     * @param \Ndm\JsonRpc2\Exception $ex
     * @return \Ndm\JsonRpc2\ResponseError
     */
    public static function fromException($id, Exception $ex)
    {
        return new self($id, $ex->getCode(), $ex->getMessage(), $ex->getData());
    }
}