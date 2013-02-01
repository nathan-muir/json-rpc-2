<?php
namespace Ndm\JsonRpc2\Server\Dispatch;

use \Ndm\JsonRpc2\Server\Exception\MethodNotFoundException;
use \Ndm\JsonRpc2\Server\Exception\RuntimeException;
use \Ndm\JsonRpc2\Server\Exception\InvalidArgumentException;

/**
 *
 *
 */

class FunctionListDispatch implements DispatchInterface
{

    /**
     * @var array
     */
    private $methodMap = array();

    /**
     * @param $methodMap
     */
    public function __construct($methodMap)
    {
        $this->methodMap = $methodMap;
    }

    /**
     * @param string $alias
     * @param array $arguments
     *
     * @throws MethodNotFoundException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     *
     * @return mixed
     */
    public function invoke($alias, $arguments)
    {
        // if the method requested is available
        if (!isset($this->methodMap[$alias])) {
            throw new MethodNotFoundException($alias);
        }

        try {
            // reflect the global function
            $reflection = new \ReflectionFunction ($this->methodMap[$alias]);

            // check the parameters in the reflection against what was sent in the request
            $arguments = $this->checkParams($reflection->getParameters(), $arguments);

            // return the result as an invoked call
            return $reflection->invokeArgs($arguments);

        } catch (\ReflectionException $rex) {
            // Propagate an appropriate exception
            throw new RuntimeException("Reflections failed on alias: {$alias}", 0, $rex);
        } catch (\Exception $ex) {
            throw new RuntimeException("Execution of method '{$alias}'  failed.", 0, $ex);
        }
    }

    /**
     * @param \ReflectionParameter[] $parameters
     * @param object|array $arguments
     * @throws InvalidArgumentException
     * @return array
     */
    private function checkParams($parameters, $arguments)
    {
        // create an array of new arguments
        $newArguments = array();
        if (is_object($arguments)) { // associative list of parameter names and their values
            // get a list of parameters, and compare against provided arguments
            $additionalArgs = array_diff(
                array_map(
                    function (\ReflectionParameter $p) {
                        return $p->getName();
                    },
                    $parameters
                ),
                array_keys(get_object_vars($arguments))
            );
            if (count($additionalArgs) > 0) {
                throw new InvalidArgumentException("Additional named arguments were supplied that are not supported.");
            }
            foreach ($parameters as $parameter) {
                // check the object for the param
                if (property_exists($arguments, $parameter->getName())) {
                    $newArguments[] = $arguments->{$parameter->getName()};
                } elseif ($parameter->isOptional()) {
                    $newArguments[] = $parameter->getDefaultValue();
                } else {
                    // if the parameter is not provided by arguments, throw an exception
                    throw new InvalidArgumentException("Required named parameter was not provided: {$parameter->getName()}");
                }
            }
        } else {
            $numArgs = count($arguments);
            if ($numArgs > count($parameters)) {
                throw new InvalidArgumentException("Additional positional arguments were supplied that are not supported.");
            }
            $currentArg = 0;
            foreach ($parameters as $parameter) {
                if ($currentArg < $numArgs) {
                    $newArguments[] = $arguments[$currentArg];
                    $currentArg++;
                } elseif ($parameter->isOptional()) {
                    $newArguments[] = $parameter->getDefaultValue();
                } else {
                    throw new InvalidArgumentException("Required positional parameter was not provided: {$currentArg}");
                }
            }
        }
        // return the new array of arguments
        return $newArguments;
    }

}
