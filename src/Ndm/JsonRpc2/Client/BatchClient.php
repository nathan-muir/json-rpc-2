<?php

namespace Ndm\JsonRpc2\Client;

use \Ndm\JsonRpc2\Core as Core;

/**
 * A client that groups calls and notifications into a batch request.
 *
 * The batch is processed when 'BatchClient->batch()' is called.
 *
 * The "user" code is responsible for handling errors related to individual requests in the batch.
 *
 */
class BatchClient
{

    /**
     * @var Transport\TransportInterface
     */
    private $transport;

    /**
     * @var Core\ResponseParser
     */
    private $parser;

    /**
     * @var Core\BatchRequest
     */
    private $batch;

    /**
     * @param Transport\TransportInterface $transport
     * @param Core\ResponseParser $parser
     */
    public function __construct(Transport\TransportInterface $transport, Core\ResponseParser $parser = null)
    {
        $this->transport = $transport;
        if ($parser === null){
            $parser = new Core\ResponseParser();
        }
        $this->parser = $parser;
        $this->reset();
    }

    /**
     * @param \Ndm\JsonRpc2\Core\BatchRequest $request
     *
     * @throws Exception\ClientException
     * @throws Exception\TransportException
     *
     *
     * @return null|\Ndm\JsonRpc2\Core\BatchResponse|\Ndm\JsonRpc2\Core\Response|\Ndm\JsonRpc2\Core\ResponseError
     */
    private function send(Core\BatchRequest $request)
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
     * Resets the batch requests
     */
    public function reset()
    {
        $this->batch = new Core\BatchRequest();
    }

    /**
     * @param string $method
     * @param array|null|\stdClass $arguments
     * @return mixed
     */
    public function call($method, $arguments = null)
    {
        // create a request object from the call parameters
        $request = new Core\Request($method, $arguments, true, $this->uuid());
        // add the request to the batch
        $this->batch->addRequest($request);
        // return the id for reference
        return $request->id;
    }

    /**
     * @param string $method
     * @param array|null|\stdClass $arguments
     */
    public function notify($method, $arguments = null)
    {
        // create a request object from the call parameters
        $request = new Core\Request($method, $arguments);
        // add the request to the batch
        $this->batch->addRequest($request);
    }

    /**
     * Sends the current batch request, and retrieves the batch response.
     *
     * Does not clear the request in the case of an exception.
     *
     * @throws Exception\ClientResponseException
     * @throws Exception\ClientException
     * @return Core\BatchResponse|Core\Response|Core\ResponseError|null
     */
    public function batch()
    {
        // check that at least one call or notify has been added to the batch
        if (!$this->batch->hasRequests()) {
            throw new Exception\ClientException("Cannot submit batch without any requests");
        }

        // send the request via the transport
        $response = $this->send($this->batch);

        // if the response was empty - ie server assumed it was a notification
        if ($response === null) {
            // however if we didn't send a notification batch- this is an exception
            if (!$this->batch->isNotification()) {
                throw new Exception\ClientException("No response was received for a batch request.");
            }
            // successful response - reset the batch
            $this->reset();
            return null;
        }
        // check for a response indicating an error
        if ($response instanceof Core\ResponseError) {
            if ($response->id !== null) {
                // in the case of an ResponseError when sending a batch
                // the response->id should always be null
                throw new Exception\ClientException("Received an unexpected response. An error was returned with a non-null ID.");
            }
            // propagate the json-rpc level exception
            throw new Exception\ClientResponseException($response->code, $response->message, $response->data);
        }
        // check for misinterpretation of request - shouldn't be a single response
        if ($response instanceof Core\Response) {
            // shouldn't receive single response for batch
            throw new Exception\ClientException("Received an unexpected response. A single non-erroneous response was received for a batch request.");
        }

        // should only be a batch response now
        assert('$response instanceof \\Ndm\\JsonRpc2\\Core\\BatchResponse');
        // successful response - clear the batch request
        $this->reset();
        // return the response
        return $response;
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
