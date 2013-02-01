<?php

namespace Ndm\JsonRpc2\Client;


/**
 * Helper class to set up a typical client scenario
 */
class HttpClient {

    /**
     * @param string $url
     * @return Client
     */
    public static function connect($url){

        $transport = new Transport\HttpStreamTransport($url);

        return new Client($transport);
    }

}