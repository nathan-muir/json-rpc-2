<?php

namespace Ndm\JsonRpc2\Core;

/**
 * Represents a batch response
 *
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

    /**
     * Finds the response with the given ID
     *
     * Need to be careful that requests don't ever have a null-id
     *
     * @param mixed $id
     *
     * @return Response|ResponseError|null
     */
    public function getResponse($id)
    {
        foreach ($this->responses as $response) {
            if ($response->id === $id) {
                return $response;
            }
        }
        return null;
    }
}
