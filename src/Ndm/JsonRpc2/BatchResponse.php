<?php

namespace Ndm\JsonRpc2;

/**
 * Represents a batch response
 *
 * @author Nathan Muir
 * @version 2012-12-24
 */
class BatchResponse implements ExportableInterface
{

    /**
     * @var Response[]|ResponseError[]
     */
    private $responses = array();

    /**
     * @param Response[]|ResponseError[] = $responses
     */
    public function __construct($responses)
    {
        $this->responses = $responses;
    }

    /**
     * @return mixed
     */
    public function toJsonNatives()
    {
        //  return an array of the rendered responses
        return array_map(
            function (ExportableInterface $r) {
                return $r->toJsonNatives();
            },
            $this->responses
        );
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return Json::encode($this->toJsonNatives());
    }
}