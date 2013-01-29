<?php

namespace Ndm\JsonRpc2;

/**
 * A Basic exception to implement Unauthorised method access.
 */
class Exception_Unauthorised extends Exception
{

    /**
     * Constructs an unathorised exception with default message & code
     */
    public function __construct()
    {
        parent::__construct("Unauthorised", 401);
    }
}