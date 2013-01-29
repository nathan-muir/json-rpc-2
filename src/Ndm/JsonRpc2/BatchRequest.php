<?php

namespace Ndm\JsonRpc2;

/**
 * Represents a batch request
 *
 * @author Nathan Muir
 * @version 2012-12-24
 */
class BatchRequest implements ExportableInterface
{

    /**
     * @var Request[]
     */
    private $requests = array();

    /**
     * @param Request[] = $requests
     */
    public function __construct($requests)
    {
        $this->requests = $requests;
    }

    /**
     * @return mixed
     */
    public function toJsonNatives()
    {
        //  return an array of the rendered requests
        return array_map(
            function (Request $r) {
                return $r->toJsonNatives();
            },
            $this->requests
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