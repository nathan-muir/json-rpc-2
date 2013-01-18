<?php

namespace JsonRpc\Transport;

/**
 * A transport class used for testing, initialise with pre-canned requests & response
 *
 * Will notify if result rendered is the same as expected.
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
     * @var string
     */
    private $desiredResponse;

    /**
     * @param string $requestData
     * @param string $desiredResponse
     */
    public function __construct($requestData, $desiredResponse)
    {
        $this->requestData = $requestData;
        if (strlen($desiredResponse) > 0) {
            $desiredResponse = json_encode(json_decode($desiredResponse));
        }
        $this->desiredResponse = $desiredResponse;

    }

    /**
     * Processes data from it's specific source, and initialise a request, or a group of requests
     *
     * @return \JsonRpc\Request|\JsonRpc\Request[]
     * @throws TransportException
     * @throws \JsonRpc\Exception
     */
    public function getRequest()
    {
        return \JsonRpc\Request::createFromString($this->requestData);
    }


    /**
     * Processes data from it's specific source, and initialise a request, or a group of requests
     *
     * @param \JsonRpc\ResponseInterface $collection
     *
     * @throws TransportException
     *
     */
    public function render(\JsonRpc\ResponseInterface $collection = null)
    {
        if ($collection === null) {
            $this->response = '';
        } else {
            $this->response = (string)$collection;
        }
    }

    /**
     * @return bool
     */
    public function checkResponse()
    {
        if (strlen($this->desiredResponse) === 0) {
            return strlen($this->response) === 0;
        } else {
            return strcmp($this->response, $this->desiredResponse) === 0;
        }
    }

    /**
     * @return string
     */
    public function getRequestData()
    {
        return $this->requestData;
    }


    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getDesiredResponse()
    {
        return $this->desiredResponse;
    }
}