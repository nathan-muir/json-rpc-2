<?php

namespace JsonRpc;

/**
 * @author Nathan Muir
 * @version 2012-12-24
 */
class Exception_MethodNotFound extends \JsonRpc\Exception
{

    /**
     * Constructs with the default error code & message
     */
    public function __construct()
    {
        parent::__construct("Method not found.", -32601);
    }
}