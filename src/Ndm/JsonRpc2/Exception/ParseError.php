<?php

namespace Ndm\JsonRpc2;

/**
 * @author Nathan Muir
 * @version 2012-12-24
 */
class Exception_ParseError extends \Ndm\JsonRpc2\Exception
{

    /**
     * @param mixed|null $data additional information about the parse error
     */
    public function __construct($data = null)
    {
        parent::__construct("Parse error.", -32700, $data);
    }
}