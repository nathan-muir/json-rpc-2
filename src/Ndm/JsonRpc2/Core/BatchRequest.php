<?php

namespace Ndm\JsonRpc2\Core;

/**
 * Represents a batch request
 *
 */
class BatchRequest implements ExportableInterface
{

    /**
     * @var Request[]
     */
    private $requests = array();

    /**
     * @var bool
     */
    private $isNotification = true;

    /**
     * @param Request[] $requests
     */
    public function __construct($requests = array())
    {
        foreach ($requests as $request) {
            $this->addRequest($request);
        }
    }

    /**
     * @param Request $request
     */
    public function addRequest(Request $request)
    {
        $this->requests[] = $request;
        $this->isNotification = $this->isNotification && $request->isNotification();
    }

    /**
     * @return bool
     */
    public function hasRequests()
    {
        return !empty($this->requests);
    }

    /**
     * @return bool
     */
    public function isNotification()
    {
        return $this->isNotification;
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
