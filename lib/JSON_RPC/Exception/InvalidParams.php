<?php

namespace JSON_RPC;
/**
 * @author Nathan Muir
 * @version 2012-12-24
 */
class Exception_InvalidParams extends \JSON_RPC\Exception {

	/**
	 * Constructs a default 'Invalid Params' message with the default message & code
	 * @param mixed|null $data
	 */
	public function __construct($data=null){
		parent::__construct("Invalid params", -32602, $data);
	}
}