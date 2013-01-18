<?php

namespace JsonRpc;

/**
 * @author Nathan Muir
 * @version 2012-12-24
 */
class Exception_InternalError extends \JsonRpc\Exception
{

    /**
     * @param mixed|null $data
     */
    public function __construct($data = null)
    {
        parent::__construct("Internal error.", -32603, $data);
    }
}