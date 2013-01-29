<?php

namespace Ndm\JsonRpc2\Client\Transport;


interface TransportInterface
{

    /**
     * @param string $request
     * @return string
     */
    public function send($request);
}