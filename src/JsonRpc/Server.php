<?php

namespace JsonRpc;

/**
 * This class brings together the transport and dispatch systems.
 *
 * It performs the collation of responses/results in to a ResponseCollection,
 *  before allowing the transport to render it as required.
 *
 * @author Nathan Muir
 * @version 2012-12-24
 */
class Server implements \Psr\Log\LoggerAwareInterface
{

    /**
     * @var \JsonRpc\Transport\TransportInterface
     */
    private $transport;

    /**
     * @var \JsonRpc\Dispatch\DispatchInterface
     */
    private $dispatch;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Constructs a new \JsonRpc\Server
     *
     * @param Transport\TransportInterface $transport
     * @param Dispatch\DispatchInterface $dispatch
     */
    public function __construct(Transport\TransportInterface $transport, Dispatch\DispatchInterface $dispatch)
    {
        $this->transport = $transport;
        $this->dispatch = $dispatch;
        $this->setLogger(new \Psr\Log\NullLogger());
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
     * @throws Transport\TransportException
     */
    public function process()
    {
        try {
            // interrogate the transport for the request
            $requestString = $this->transport->receive();
            // instantiate the request as PHP objects from a string
            $request = Request::createFromString($requestString);
            // process the request, and obtain the response
            $response = $this->getResponse($request);
        } catch (\JsonRpc\Exception $tex) {
            $response = ResponseError::fromException(null, $tex);
        } catch (\Exception $ex) {
            $response = ResponseError::fromException(null, new Exception_InternalError());
        }
        // response is null - iff ?
        if ($response === null) {
            $this->transport->reply('');
        } else {
            $this->transport->reply((string)$response);
        }
    }

    /**
     *
     *
     * @param $request Request|Request[]|null
     * @return ResponseInterface|null
     */
    private function getResponse($request)
    {
        // if $request === null || $request instanceof \stdClass
        if (!is_array($request)) {
            return $this->processRequest($request);
        } // else if it's an array of requests
        else {
            // process each request
            $responses = array();
            foreach ($request as $r) {
                $response = $this->processRequest($r);
                if ($response !== null) { // returns null for notifications
                    $responses[] = $response;
                }
            }

            if (empty($responses)) {
                // rendering a null response - means all were notifications
                return null;
            } else {
                return new BatchResponse($responses);
            }
        }
    }

    /**
     * @param Request $request
     * @return Response|ResponseError|null
     */
    private function processRequest(Request $request = null)
    {
        if ($request === null) {
            return ResponseError::fromException(null, new Exception_InvalidRequest());
        }

        if ($request->isNotification()) {
            // invoke, and ignore all errors
            try {
                $this->dispatch->invoke($request->method, $request->params);
            } catch (\Exception $ex) { /* intentionally empty */
            }
            return null;
        } else {
            try {
                $result = $this->dispatch->invoke($request->method, $request->params);
                $response = new Response($request->id, $result);
            } catch (\JsonRpc\Exception $ex) { //notation used for clarity
                // Note: Any Exception wrapped in \JsonRpc\Exception will be exposed in the response
                //  You can/should configure your Dispatch object to wrap exceptions that you want exposed, rather than
                //  throw this type of exception in your application code.
                $response = ResponseError::fromException($request->id, $ex);
            } catch (\Exception $ex) {
                // Note: Any Exception NOT wrapped in \JsonRpc\Exception will NOT be exposed in the response
                // TODO: Check if this should use (-32603, "Internal Error") as per http://www.jsonrpc.org/specification#error_object
                $response = new ResponseError($request->id, 500, "Error");
            }
            return $response;
        }
    }

}