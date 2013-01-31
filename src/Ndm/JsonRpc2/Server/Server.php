<?php

namespace Ndm\JsonRpc2\Server;

use \Ndm\JsonRpc2\Core as Core;

/**
 * This class brings together the transport and dispatch systems.
 *
 * It performs the collation of responses/results in to a ResponseCollection,
 *  before allowing the transport to render it as required.
 *
 */
class Server implements \Psr\Log\LoggerAwareInterface
{

    /**
     * @var Transport\TransportInterface
     */
    private $transport;

    /**
     * @var Dispatch\DispatchInterface
     */
    private $dispatch;

    /**
     * @var Core\RequestParser
     */
    private $parser;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Constructs a new \Ndm\JsonRpc2\Server\Server
     *
     * @param Transport\TransportInterface $transport
     * @param Dispatch\DispatchInterface $dispatch
     */
    public function __construct(Transport\TransportInterface $transport, Dispatch\DispatchInterface $dispatch)
    {
        $this->transport = $transport;
        $this->dispatch = $dispatch;
        $this->setLogger(new \Psr\Log\NullLogger());
        $this->parser = new Core\RequestParser();
    }

    /**
     * Sets a logger instance on the object
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @return null
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Retrieves the request from the transport, obtains the response, and replies with the response.
     *
     * Transport layer will always be called - unless there is an exception on receive.
     *
     * Note: This WILL propagate transport layer exception on receive AND reply.
     *  Correct handling code, in the case of 'HttpTransport' should respond with HTTP/1.0 400 Bad Request or similar
     *
     * @throws Exception\TransportException
     *
     */
    public function process()
    {
        try {
            // obtain the parsed requests from the transport layer & parser
            $request = $this->receive();
            // process the request, and obtain the response
            $response = $this->getResponse($request);
        } catch (Core\Exception\JsonParseException $jpe) {
            // can be thrown by this->receive()
            $this->logger->warning(
                "Received a parse exception when decoding request.",
                array('error_message' => $jpe->getMessage())
            );
            $response = Core\ResponseError::createParseError();
        }

        if ($response === null) {
            // this is the case for notifications, or a batch of notifications
            $this->transport->reply('');
        } else {
            $this->transport->reply($response->toJson());
        }
    }

    /**
     * @throws Exception\TransportException
     * @throws Core\Exception\JsonParseException
     *
     * @return Core\Request|Core\Request[]|null
     */
    private function receive()
    {
        // interrogate the transport for the request
        $requestString = $this->transport->receive();
        // instantiate the request as PHP objects from a string
        return $this->parser->parse($requestString);
    }

    /**
     * Processes a request sequence, and returns an appropriate response.
     *
     * @param $request Core\Request|Core\Request[]|null
     * @return Core\Response|Core\ResponseError|Core\BatchResponse|null
     */
    private function getResponse($request)
    {
        // if there is no request parsed, it is invalid
        if ($request === null) {
            return Core\ResponseError::createInvalidRequest();
        }
        // if there's a single request, process accordingly
        if ($request instanceof Core\Request) {
            if ($request->isNotification()) {
                // do not respond to notifications
                $this->invokeIgnore($request);
                return null;
            } else {
                return $this->invoke($request);
            }
        }
        // the request must be a batch
        // process each request
        $responses = array();
        foreach ($request as $singleRequest) {
            if ($singleRequest === null) {
                // an invalid request in the batch request - add a response error to the batch response
                $responses[] = Core\ResponseError::createInvalidRequest();
            } else {
                if ($singleRequest->isNotification()) {
                    // do not add notifications to batch response
                    $this->invokeIgnore($singleRequest);
                } else {
                    $responses[] = $this->invoke($singleRequest);
                }
            }
        }
        if (empty($responses)) {
            // if all requests in the batch were notifications - no response
            return null;
        }
        // otherwise, return a batch of responses
        return new Core\BatchResponse($responses);
    }

    /**
     * Invokes a request, clearing all exceptions, and not checking for a result
     * @param \Ndm\JsonRpc2\Core\Request $request
     */
    private function invokeIgnore(Core\Request $request)
    {
        try {
            $this->dispatch->invoke($request->method, $request->params);
        } catch (Exception\RuntimeException $rx) {
            /* intentionally empty */
        }
    }

    /**
     * Invokes the request, tries to return the result, or a suitable response-error object
     * @param Core\Request $request
     * @return Core\Response|Core\ResponseError
     */
    private function invoke(Core\Request $request)
    {
        try {
            $result = $this->dispatch->invoke($request->method, $request->params);
            $response = new Core\Response($request->id, $result);
        } catch (Exception\MethodNotFoundException $mfx) {
            // respond with appropriate error codes
            $response = Core\ResponseError::createMethodNotFound($request->id);
        } catch (Exception\InvalidArgumentException $iax) {
            // respond with appropriate error codes
            $response = Core\ResponseError::createInvalidParams($request->id);
        } catch (Exception\ResponseExceptionInterface $rxi) {
            // convert ResponseExceptionInterface => Response Error Object for any custom defined Exceptions sent from the Dispatch Layer
            $response = new Core\ResponseError($request->id, $rxi->getErrorCode(), $rxi->getErrorMessage(
            ), $rxi->getErrorData());
        } catch (Exception\RuntimeException $rx) {
            // dispatch will wrap all exceptions from invoke - in a runtime exception
            // check to see if the exception implements interface for translation in to JSON-RPC Error Object
            $rxi = $rx->getPrevious();
            if ($rxi !== null && $rxi instanceof Exception\ResponseExceptionInterface) {
                $response = new Core\ResponseError($request->id, $rxi->getErrorCode(), $rxi->getErrorMessage(
                ), $rxi->getErrorData());
            } else {
                $response = Core\ResponseError::createInternalError($request->id);
            }
        }
        return $response;
    }

}