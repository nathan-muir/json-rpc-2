<?php

namespace Ndm\JsonRpc2\Server\Transport;

/**
 * A transport class used for testing, initialise with pre-canned requests & response
 *
 *
 * @author Nathan Muir
 * @version 2012-12-24
 */
class TestTransport implements TransportInterface
{

    /**
     * @var string
     */
    private $requestData;

    /**
     * @var string
     */
    private $response;

    /**
     * @param string $requestData
     */
    public function __construct($requestData)
    {
        $this->requestData = $requestData;
    }

    /**
     * @return string
     */
    public function receive()
    {
        return $this->requestData;
    }

    /**
     * @param string $response
     */
    public function reply($response)
    {
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

}