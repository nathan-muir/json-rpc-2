<?php

namespace Ndm\JsonRpc2\Client;

/**
 * A client which transposes invokation via __call to the Client->call() method
 */
class NativeClient {

    /**
     * @var Client
     */
    private $client;
    /**
     * @param Client $client
     */
    public function __construct(Client $client){
        $this->client = $client;
    }

    /**
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments){
        return $this->client->call($method, $arguments);
    }
}