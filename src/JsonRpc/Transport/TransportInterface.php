<?php

namespace JsonRpc\Transport;

interface TransportInterface
{

    /**
     * Processes data from it's specific source, and initialise a request, or a group of requests
     *
     * @return \JsonRpc\Request|\JsonRpc\Request[]
     * @throws TransportException
     * @throws \JsonRpc\Exception
     */
    public function getRequest();


    /**
     * Processes data from it's specific source, and initialise a request, or a group of requests
     *
     * @param \JsonRpc\ResponseInterface $collection
     *
     * @throws TransportException
     */
    public function render(\JsonRpc\ResponseInterface $collection = null);


}