<?php

namespace JSON_RPC;
/**
 * @author Nathan Muir
 * @version 2012-12-24
 */
class Exception_InvalidRequest extends \JSON_RPC\Exception {

	/**
	 * Constructs an 'Invalid Request' error with the default message & code
	 */
	public function __construct(){
		parent::__construct("Invalid Request.", -32600);
	}
}