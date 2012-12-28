<?php

namespace JSON_RPC;
/**
 * @author Nathan Muir
 * @version 2012-12-24
 */
class Exception_ParseError extends \JSON_RPC\Exception {

	/**
	 * @param mixed|null $data additional information about the parse error
	 */
	public function __construct($data=null){
		parent::__construct("Parse error.", -32700, $data);
	}
}