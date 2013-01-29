<?php

namespace Ndm\JsonRpc2\Client;

use \Ndm\JsonRpc2\Request;
use \Ndm\JsonRpc2\BatchRequest;
use \Ndm\JsonRpc2\Exception;
use \Ndm\JsonRpc2\Response;
use \Ndm\JsonRpc2\BatchResponse;
use \Ndm\JsonRpc2\ResponseError;

/**
 * @author Nathan Muir
 * @version 2013-01-29
 */
class Client
{

    const REQUEST_ID_INTEGER = 1;
    const REQUEST_ID_UUID = 2;

    /**
     * @var \Ndm\JsonRpc2\Client\Transport\TransportInterface
     */
    private $transport;


    /**
     * @var int
     */
    private $id = 1;

    /**
     * @var int
     */
    private $idMode;

    /**
     * @var ResponseParser
     */
    private $parser;

    /**
     * @param Transport\TransportInterface $transport
     * @param int $idMode
     */
    public function __construct(Transport\TransportInterface $transport, $idMode = self::REQUEST_ID_UUID)
    {
        $this->transport = $transport;
        $this->idMode = $idMode;

        $this->parser = new ResponseParser();
    }

    /**
     * @param string $method
     * @param null|array|\stdClass $arguments
     * @return mixed
     */
    public function call($method, $arguments = null)
    {

        // create a request object from the call parameters
        $request = new Request($method, $arguments, true, $this->getID());

        // send the request via the transport
        $responseJson = $this->transport->send($request->toJson());

        // parse the request json
        $response = $this->parser->parse($responseJson);

        // if the response was empty - ie server assumed it was a notification
        if ($response === null) {
            throw new \Ndm\JsonRpc2\Exception("", -1);
        }
        // if the response is a batch- what the hell
        if ($response instanceof BatchResponse) {
            throw new Exception("", -1);
        }
        // if the response was an error- throw a suitable exception
        if ($response instanceof ResponseError) {
            if ($response->id !== $request->id) {
                // this should only happen for parser or invalid request errors
            }
            // propagate the json-rpc level exception
            throw new Exception("", -1);
        }
        // if the response id doesn't match
        if ($response->id !== $request->id) {
            throw new Exception("", -1);
        }

        assert('$response instanceof \\Ndm\\JsonRpc2\\Response');
        // finally: return the result
        return $response->result;
    }

    public function notify($method, $arguments = null)
    {
        // create a request object from the call parameters
        $request = new Request($method, $arguments, false);

        // send the request via the transport
        $responseJson = $this->transport->send($request->toJson());

        // parse the request json
        $response = $this->parser->parse($responseJson);

        // there should be no response in the case of notifications
        if ($response === null) {
            return;
        }
        // there may have been some error in interpreting the request
        if ($response instanceof ResponseError) {
            throw new Exception("", -1);
        }

        // what the hell was the response?
        throw new Exception("", -1);
    }

    public function batch($requests)
    {
        // detect use of variadic arguments
        if (func_num_args() > 1) {
            $requests = func_get_args();
        }

        // squash in to batch request
        if (!($requests instanceof BatchRequest)) {
            $requests = new BatchRequest($requests);
        }

        // send the request via the transport
        $responseJson = $this->transport->send($requests->toJson());

        // parse the request json
        $response = $this->parser->parse($responseJson);

        // if the response was empty - ie server assumed it was a notification
        if ($response === null) {
            if (!$requests->isNotification()) { // TODO implement BatchRequest->isNotification()
                throw new Exception("", -1);
            }
            return null; // TODO decide NULL or empty array|iterable
        }
        // check for a response indicating an error
        if ($response instanceof ResponseError) {
            if ($response->id !== null) {
                // in the case of an ResponseError when sending a batch
                // the response->id should always be null
            }
            // propagate the json-rpc level exception
            throw new Exception("", -1);
        }
        // check for misinterpretation of request - shouldn't be a single response
        if ($response instanceof Response) {
            // shouldn't receive single response for batch
            throw new Exception("", -1);
        }

        // should only be a batch response now
        assert('$response instanceof \\Ndm\\JsonRpc2\\BatchResponse');

        // TODO: How to bind the output result correctly?
        // - Callbacks?
        // - Special Request->response bindings (eg. $request->getResponse())
        // - User can 'lookup' response in BatchResponse (eg $batchResponse->getResponse($request->id));
        // - Iterable "Hash|Map" with the request as key-> response as value
        return $response;
    }

    private function getID()
    {
        if (self::REQUEST_ID_INTEGER == $this->idMode) {
            return $this->id++;
        } else {
            return $this->uuid();
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