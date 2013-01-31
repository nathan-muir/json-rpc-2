<?php

namespace Ndm\JsonRpc2\Server\Exception;

/**
 * A generic class that can be used by client code to implement exceptions that are converted
 *  in to JSON-RPC compatible exceptions.
 */
class ResponseException extends RuntimeException implements ResponseExceptionInterface
{

    /**
     * @var mixed
     */
    private $data;

    /**
     * @param string $message
     * @param int $code
     * @param mixed $data
     */
    public function __construct($message, $code, $data = null)
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->getMessage();
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->getCode();
    }

    /**
     * @return mixed
     */
    public function getErrorData()
    {
        return $this->data;
    }
}
