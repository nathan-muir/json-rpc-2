<?php

namespace Ndm\JsonRpc2\Client\Exception;

/**
 * Encapsulates the JSON-RPC Spec "Error" response.
 */
class ClientResponseException extends ClientException
{

    /**
     * @var mixed
     */
    private $data;

    /**
     * @param int $code
     * @param string $message
     * @param null $data
     */
    public function __construct($code, $message, $data = null)
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
