<?php

namespace Ndm\JsonRpc2;

/**
 * An object representation of a single request
 *
 * @author Matt Morley (MPCM)
 * @author Nathan Muir
 * @version 2012-12-24
 *
 */
class Request implements ExportableInterface
{
    /**
     * @var string|int|null
     */
    public $id = null;

    /**
     * @var bool
     */
    public $hasId = false;
    /**
     * @var array|null
     */
    public $params = null;

    /**
     * @var string
     */
    public $method;

    /**
     * @param string $method
     * @param array|\stdClass $params
     * @param bool $hasId As the $id can be null, this is to indicate whether the request has an id
     * @param int|null|string|float $id
     */
    public function __construct($method, $params = null, $hasId = false, $id = null)
    {
        $this->method = $method;
        $this->params = $params;
        if ($hasId) {
            $this->hasId = true;
            $this->id = $id;
        }
    }

    /**
     * @return bool
     */
    public function isNotification()
    {
        return !$this->hasId;
    }

    /**
     * @return mixed
     */
    public function toJsonNatives()
    {
        $render = array(
            "jsonrpc" => "2.0",
            "method" => $this->method
        );
        if (isset($this->params)) {
            $render['params'] = $this->params;
        }
        if ($this->hasId) {
            $render['id'] = $this->id;
        }
        return $render;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return Json::encode($this->toJsonNatives());
    }
}