<?php

namespace Ndm\JsonRpc2\Server\Exception;

/**
 *
 *
 */
class MethodNotFoundException extends RuntimeException
{

    /**
     * @param string $methodName
     */
    public function __construct($methodName)
    {
        parent::__construct(sprintf("method not found: %s", var_export($methodName, true)));
    }
}
