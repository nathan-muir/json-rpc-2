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
class Server
{

    /**
     * @var \JsonRpc\Transport\TransportInterface
     */
    protected $transport;

    /**
     * @var \JsonRpc\Dispatch\DispatchInterface
     */
    protected $dispatch;

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
    }


    /**
     * Retrieves the request from the transport
     * @return ResponseInterface
     */
    public function process()
    {
        $response = $this->getResponse();
        $this->transport->render($response);
        return $response; // TODO decide if this is necessary
    }

    /**
     * This method performs the main processing of the class
     *
     * @return ResponseInterface
     */
    private function getResponse()
    {
        // interrogate the transport for the request collection
        try {
            $request = $this->transport->getRequest();
        } catch (\JsonRpc\Exception $tex) {
            return ResponseError::fromException(null, $tex);
        } catch (\Exception $ex) {
            return ResponseError::fromException(null, new Exception_InternalError());
        }
        // process the request (could be Request|Request[])
        if (is_object($request)) { // if $request === Request
            return $this->processRequest($request);
        } else { // else if it's an array of requests
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
    private function processRequest(Request $request)
    {
        if (!$request->valid) {
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