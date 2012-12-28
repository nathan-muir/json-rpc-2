<?php
namespace JSON_RPC\Dispatch;
/**
 * @author Nathan Muir
 * @version 2012-12-24
 */

class FunctionListDispatch implements DispatchInterface {

	/**
	 * @var array
	 */
	private $methodMap;

	/**
	 * @param array $methodMap
	 */
	public function __construct($methodMap){
		$this->methodMap = $methodMap;
	}


	/**
	 * @param string $method
	 * @param array $args
	 *
	 * @throws \JSON_RPC\Exception_InvalidParams
	 * @throws \JSON_RPC\Exception_MethodNotFound
	 * @throws \JSON_RPC\Exception_InternalError
	 *
	 * @return mixed
	 */
	public function invoke( $method, $args ) {

		// if the method requested is available
		if( !isset( $this->methodMap[$method] ) ) {
			throw new \JSON_RPC\Exception_MethodNotFound();
		}

		try{
			// reflect the global function
			$reflection = new \ReflectionFunction ( $this->methodMap[$method] );

			// check the parameters in the reflection against what was sent in the request
			$args = $this->checkParams( $reflection->getParameters(), $args );

			// return the result as an invoked call
			return $reflection->invokeArgs ( $args );

		} catch ( \ReflectionException $rex ) {
			// NOTE: could add $rex->getMessage() to the data of the internal error, however not suitable for production
			throw new \JSON_RPC\Exception_InternalError();
		}
	}

	/**
	 * @param \ReflectionParameter[] $parameters
	 * @param object|array $arguments
	 * @throws \JSON_RPC\Exception_InvalidParams
	 * @return array
	 */
	private function checkParams($parameters, $arguments) {
		// create an array of new arguments
		$newArguments = array();
		if (is_object($arguments)){ // associative list of parameter names and their values
			// get a list of parameters, and compare against provided arguments
			$additionalArgs = array_diff(array_map(function(\ReflectionParameter $p) { return $p->getName(); }, $parameters), array_keys(get_object_vars($arguments)));
			if (count($additionalArgs) > 0){
				throw new \JSON_RPC\Exception_InvalidParams();
			}
			foreach ($parameters as $parameter) {
				// check the object for the param
				if (property_exists($arguments, $parameter->getName())) {
					$newArguments[] = $arguments->{$parameter->getName()};
				} elseif ($parameter->isOptional()) {
					$newArguments[] = $parameter->getDefaultValue();
				} else{
					// if the parameter is not provided by arguments, throw an exception
					throw new \JSON_RPC\Exception_InvalidParams();
				}
			}
		} else {
			$numArgs = count($arguments);
			if ($numArgs > count($parameters)){
				throw new \JSON_RPC\Exception_InvalidParams();
			}
			$currentArg = 0;
			foreach ($parameters as $parameter) {
				if ($currentArg < $numArgs) {
					$newArguments[] = $arguments[$currentArg];
					$currentArg++;
				} elseif ($parameter->isOptional()) {
					$newArguments[] = $parameter->getDefaultValue();
				} else {
					throw new \JSON_RPC\Exception_InvalidParams();
				}
			}
		}
		// return the new array of arguments
		return $newArguments;
	}

}