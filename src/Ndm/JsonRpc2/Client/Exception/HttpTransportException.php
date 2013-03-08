<?php

namespace Ndm\JsonRpc2\Client\Exception;

/**
 * Wrapper for transport based exceptions
 */
class HttpTransportException extends TransportException
{
    /**
     * @var string
     */
    private $statusMessage;
    /**
     * @var string
     */
    private $content;
    
    /**
     * @param int $statusCode
     * @param string $statusMessage
     * @param string $content
     */
    public function __construct($statusCode, $statusMessage, $content=''){
        parent::__construct("Request failed, received: {$statusCode} {$statusMessage}", $statusCode);
        $this->statusMessage = $statusMessage;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getStatusMessage(){
        return $this->statusMessage;
    }
    
    /**
     * @return string 
     */
    public function getContent(){
        return $this->content;
    }
}
