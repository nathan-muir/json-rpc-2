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

    /**
     * @param string $url
     * @param string $username
     * @param string $password
     *
     * @return Client
     */
    public static function connectBasicAuth($url, $username, $password){
        $transport = new Transport\HttpStreamTransport($url);

        $transport->setHttpContextOption('header', array('Authorization: Basic ' . base64_encode("{$username}:{$password}")));

        return new Client($transport);
    }

    /**
     * If access point is 2-legged, don't provide a token
     *
     * @param string $url
     * @param \Ndm\OAuth\Consumer $consumer
     * @param \Ndm\OAuth\Token $token
     * @param string $realm
     * @return Client
     */
    public static function connectOAuth($url, \Ndm\OAuth\Consumer $consumer, \Ndm\OAuth\Token $token=null, $realm=""){
        $transport = new Transport\OAuthHttpStreamTransport($url, $consumer, $token, $realm);

        return new Client($transport);
    }

}