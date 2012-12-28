<?php

namespace JSON_RPC;

/**
 * A basic class that encapsulates an erroneous response
 *
 * Also contains additional constructor methods for standard error types
 *
 * @author Nathan Muir
 * @version 2012-12-24
 */
class ResponseError implements ResponseInterface {

	/**
	 * @var int|null
	 */
	private $id = null;
	/**
	 * @var int
	 */
	private $code;

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @var mixed|null
	 */
	private $data;


	/**
	 * @param int|null $id
	 * @param int $code
	 * @param string $message
	 * @param mixed|null $data
	 */
	public function __construct($id, $code, $message, $data=null){
		$this->id = $id;
		$this->code = $code;
		$this->message = $message;
		$this->data = $data;
	}

	/**
	 * @return array
	 */
	public function render()
	{
		$error = array(
			"jsonrpc" => "2.0",
			"error" => array( "code" => $this->code, "message" => $this->message),
			"id" => $this->id
		);
		if (isset($this->data)){
			$error['data'] = $this->data;
		}
		return $error;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return json_encode($this->render());
	}


	/**
	 * @param int|null $id
	 * @param \JSON_RPC\Exception $ex
	 * @return \JSON_RPC\ResponseError
	 */
	public static function fromException($id, Exception $ex){
		return new self($id, $ex->getCode(), $ex->getMessage(), $ex->getData());
	}
}