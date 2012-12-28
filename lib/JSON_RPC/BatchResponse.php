<?php

namespace JSON_RPC;

/**
 * Represents a batch response
 *
 * @author Nathan Muir
 * @version 2012-12-24
 */
class BatchResponse implements ResponseInterface {

	/**
	 * @var ResponseInterface[]
	 */
	private $responses = array();

	/**
	 * @param ResponseInterface[] = $responses
	 */
	public function __construct($responses){
		$this->responses = $responses;
	}

	/**
	 * @return mixed
	 */
	public function render(){
		//  return an array of the rendered responses
		return array_map(function(ResponseInterface $r) { return $r->render(); }, $this->responses);
	}

	/**
	 * @return string
	 */
	public function __toString(){
		return json_encode($this->render());
	}
}