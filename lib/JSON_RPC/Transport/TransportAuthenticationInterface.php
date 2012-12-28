<?php

namespace JSON_RPC\Transport;


interface TransportAuthenticationInterface {


	/**
	 * @return \JSON_RPC\AuthenticationInterface
	 */
	public function authenticate();
}