<?php

namespace Ndm\JsonRpc2\Server\Transport;

/**
 * @author Nathan Muir
 * @version 2012-12-24
 */
interface TransportInterface
{

    /**
     * Processes data from it's specific source, and return data or throw a \Ndm\JsonRpc2\Exception.
     *
     * The exception thrown should generate a suitable response for the client. If it's not suitable to respond to the client
     * a TransportException should be generated.
     *
     * @return string
     *
     * @throws \Ndm\JsonRpc2\Exception
     */
    public function receive();


    /**
     * Reply to the JSON-RPC request via the transport.
     *
     * @param string $response
     *
     * @throws TransportException
     */
    public function reply($response);


}