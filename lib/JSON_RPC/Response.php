<?php

namespace JSON_RPC;
/**
 * A basic class that encapsulates a valid (non-erronous) response
 *
 * @author Nathan Muir
 * @version 2012-12-24
 */
class Response implements ResponseInterface {

	/**
	 * @var int
	 */
	private $id;
	/**
	 * @var mixed
	 */
	private $result;

	/**
	 * @param int $id
	 * @param mixed $result
	 */
	public function __construct($id, $result){
		$this->id = $id;
		$this->result = $result;
	}

	/**
	 * @return array
	 */
	public function render(){
		return array(
			"jsonrpc"=>"2.0",
			"result"=>$this->result,
			"id"=>$this->id
		);
	}

	/**
	 * @return string
	 */
	public function __toString(){
		return json_encode($this->render());
	}
}