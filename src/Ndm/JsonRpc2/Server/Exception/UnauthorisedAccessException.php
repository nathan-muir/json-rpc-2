<?php

namespace Ndm\JsonRpc2\Server\Exception;

/**
 * A Basic exception to implement Unauthorised method access.
 */
class UnauthorisedAccessException extends RuntimeException implements ResponseExceptionInterface
{
    /**
     * @param string $methodName
     */
    public function __construct($methodName)
    {
        parent::__construct(sprintf("authorisation check failed for method: %s", var_export($methodName, true)));
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return "Unauthorised Access.";
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return 401;
    }

    /**
     * @return null
     */
    public function getErrorData()
    {
        return null;
    }
}
