<?php

namespace JSON_RPC\Dispatch;
/**
 * Base interface for dispatch systems to implement.
 *
 * Can be used to control implementation of method & parameter binding
 *
 * @author Nathan Muir
 * @version 2012-12-24
 */
interface DispatchInterface {

	/**
	 * @param string $method
	 * @param array $args
	 *
	 * @throws \JSON_RPC\Exception_InvalidParams
	 * @throws \JSON_RPC\Exception_MethodNotFound
	 * @throws \JSON_RPC\Exception
	 *
	 * @return mixed
	 */
	public function invoke($method, $args);

}