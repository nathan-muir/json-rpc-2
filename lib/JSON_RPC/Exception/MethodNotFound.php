<?php

namespace JSON_RPC;
/**
 * @author Nathan Muir
 * @version 2012-12-24
 */
class Exception_MethodNotFound extends \JSON_RPC\Exception {

	/**
	 * Constructs with the default error code & message
	 */
	public function __construct(){
		parent::__construct("Method not found", -32601);
	}
}