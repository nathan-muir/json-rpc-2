<?php

namespace Ndm\JsonRpc2\Client;

use \Ndm\JsonRpc2\Core as Core;

/**
 *
 *
 */
class Client
{

    /**
     * @var \Ndm\JsonRpc2\Client\Transport\TransportInterface
     */
    private $transport;

    /**
     * @var \Ndm\JsonRpc2\Core\ResponseParser
     */
    private $parser;

    /**
     * @param Transport\TransportInterface $transport
     */
    public function __construct(Transport\TransportInterface $transport)
    {
        $this->transport = $transport;
        $this->parser = new Core\ResponseParser();
    }

    /**
     * @param \Ndm\JsonRpc2\Core\Request $request
     *
     * @throws Exception\ClientException
     * @throws Exception\TransportException
     *
     *
     * @return null|\Ndm\JsonRpc2\Core\BatchResponse|\Ndm\JsonRpc2\Core\Response|\Ndm\JsonRpc2\Core\ResponseError
     */
    private function send(Core\Request $request)
    {
        // send the request via the transport
        $responseJson = $this->transport->send($request->toJson());

        try {
            // parse the request json
            $response = $this->parser->parse($responseJson);
        } catch (Core\Exception\JsonParseException $jpe) {
            // encapsulate parsing exceptions as client exceptions
            throw new Exception\ClientException("Failed to parse response from server.", 0, $jpe);
        } catch (Core\Exception\InvalidResponseException $ire) {
            // encapsulate parse-decode exceptions as client exceptions
            throw new Exception\ClientException("The response received from the server was invalid.", 0, $ire);
        }
        // return the response
        return $response;
    }


    /**
     * @param string $method
     * @param null|array|\stdClass $arguments
     *
     * @throws Exception\ClientException
     * @throws Exception\TransportException
     * @throws Exception\ClientResponseException
     * @throws Exception\ClientResponseIdMismatchException
     *
     * @return mixed
     */
    public function call($method, $arguments = null)
    {
        // create the ID for the request - need to check against this later
        $id = $this->uuid();
        // create & send a request object from the call parameters
        $response = $this->send(new Core\Request($method, $arguments, true, $id));
        // if the response was empty - ie server assumed it was a notification
        if ($response === null) {
            throw new Exception\ClientException("The was no response to the json-rpc call");
        }
        // if the response is a batch- what the hell
        if ($response instanceof Core\BatchResponse) {
            throw new Exception\ClientException("The response was of an unexpected type");
        }
        // if the response was an error- throw a suitable exception
        if ($response instanceof Core\ResponseError) {
            // create an exception from the ResponseError object
            $responseException = new Exception\ClientResponseException($response->code, $response->message, $response->data);
            if ($response->id !== $id) {
                // this should only happen for parser or invalid request errors
                // note: may false negative for these cases if request->id is null [which shouldn't happen in this implementation]
                throw new Exception\ClientException("The request was malformed, or the server replied erroneously", 0, $responseException);
            }
            // propagate the json-rpc level exception
            throw $responseException;
        }

        assert('$response instanceof \\Ndm\\JsonRpc2\\Core\\Response');

        // if the response id doesn't match
        if ($response->id !== $id) {
            throw new Exception\ClientResponseIdMismatchException($id, $response->id);
        }

        // finally: return the result
        return $response->result;
    }

    /**
     * @param string $method
     * @param null|array|\stdClass $arguments
     *
     * @throws Exception\ClientException
     * @throws Exception\TransportException
     * @throws Exception\ClientResponseException
     *
     * @return void
     */
    public function notify($method, $arguments = null)
    {
        // create & send a request object from the call parameters
        $response = $this->send(new Core\Request($method, $arguments));

        // there should be no response in the case of notifications
        if ($response !== null) {
            // there may have been some error in interpreting the request
            if ($response instanceof Core\ResponseError) {
                throw new Exception\ClientResponseException($response->code, $response->message, $response->data);
            }

            // what the hell was the response?
            throw new Exception\ClientException("Received a response to a notification");
        }
    }

    /**
     *
     * Generate v4 UUID
     *
     * Version 4 UUIDs are pseudo-random.
     */
    private function uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
