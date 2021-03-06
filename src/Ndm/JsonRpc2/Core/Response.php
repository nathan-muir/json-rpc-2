<?php

namespace Ndm\JsonRpc2\Core;

/**
 * A basic class that encapsulates a valid (non-erroneous) response
 *
 */
class Response implements ExportableInterface
{

    /**
     * @var int
     */
    public $id;
    /**
     * @var mixed
     */
    public $result;

    /**
     * @param int $id
     * @param mixed $result
     */
    public function __construct($id, $result)
    {
        $this->id = $id;
        $this->result = $result;
    }

    /**
     * @return array
     */
    public function toJsonNatives()
    {
        return array(
            "jsonrpc" => "2.0",
            "result" => $this->result,
            "id" => $this->id
        );
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return Json::encode($this->toJsonNatives());
    }
}
