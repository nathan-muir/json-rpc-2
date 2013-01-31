<?php

namespace Ndm\JsonRpc2\Client\Transport;


/**
 *
 */
interface TransportInterface
{

    /**
     * @param string $request
     *
     * @throws \Ndm\JsonRpc2\Client\Exception\TransportException
     *
     * @return string
     */
    public function send($request);
}
