<?php

namespace JSON_RPC\Transport;
/**
 * A transport class used for testing, initialise with pre-canned requests & response
 *
 * Will notify if result rendered is the same as expected.
 *
 * @author Nathan Muir
 * @version 2012-12-24
 */
class TestTransport implements TransportInterface {

	/**
	 * @var string
	 */
	private $request;

	/**
	 * @var string
	 */
	private $desiredResponse;

	/**
	 * @param string $request
	 * @param string $desiredResponse
	 */
	public function __construct($request, $desiredResponse){
		$this->request = $request;
		$this->desiredResponse = $desiredResponse;
	}

	/**
	 * Processes data from it's specific source, and initialise a request, or a group of requests
	 *
	 * @return \JSON_RPC\Request|\JSON_RPC\Request[]
	 * @throws TransportException
	 * @throws \JSON_RPC\Exception
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Processes data from it's specific source, and initialise a request, or a group of requests
	 *
	 * @param \JSON_RPC\ResponseInterface $collection
	 *
	 * @throws TransportException
	 */
	public function render(\JSON_RPC\ResponseInterface $collection = null)
	{
		if ($collection === null){
			$response = '';
		} else {
			$response = (string) $collection;
		}

		if (strcmp($response, $this->desiredResponse) !== 0){
			throw new TransportException(
				array(
					"request"  => $this->request,
					"expected" => $this->desiredResponse,
					"received" => $response
				)
			);
		}
	}
}