<?php
namespace Ndm\JsonRpc2\Client\Exception;

/**
 *
 */
class ClientResponseIdMismatchException extends ClientException
{


    /**
     * @param mixed $requestId
     * @param mixed $responseId
     */
    public function __construct($requestId, $responseId)
    {
        $message = "The request and response ID's do not match [\"RequestID\"=>%s, \"ResponseID\"=>%s]";
        $message = sprintf($message, var_export($requestId, true), var_export($responseId, true));
        parent::__construct($message);
    }
}
