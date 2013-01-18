<?php

namespace JsonRpc;

/**
 * Basic exception class to encapsulate exceptions produced by the library.
 *
 * The code & message used will be produced in json-rpc errors
 *
 * @author Nathan Muir
 * @version 2012-12-24
 */
class Exception extends \Exception
{

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @param string $message
     * @param int $code
     * @param mixed|null $data
     */
    public function __construct($message, $code, $data = null)
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }

    /**
     * @return mixed|null
     */
    public function getData()
    {
        return $this->data;
    }
}