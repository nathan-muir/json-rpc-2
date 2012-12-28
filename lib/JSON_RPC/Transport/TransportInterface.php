<?php

namespace JSON_RPC\Transport;

interface TransportInterface {

	/**
	 * Processes data from it's specific source, and initialise a request, or a group of requests
	 *
	 * @return \JSON_RPC\Request|\JSON_RPC\Request[]
	 * @throws TransportException
	 * @throws \JSON_RPC\Exception
	 */
	public function getRequest();


	/**
	 * Processes data from it's specific source, and initialise a request, or a group of requests
	 *
	 * @param \JSON_RPC\ResponseInterface $collection
	 *
	 * @throws TransportException
	 */
	public function render(\JSON_RPC\ResponseInterface $collection=null);


}