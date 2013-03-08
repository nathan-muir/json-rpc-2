<?php
namespace Ndm\JsonRpc2\Server\Dispatch;

use \Ndm\JsonRpc2\Server\Exception\RuntimeException;
use \Ndm\JsonRpc2\Server\Exception\InvalidArgumentException;

/**
 * A basic implementation, which uses reflections on invocation to parse & check arguments
 *
 */
class ReflectionMethod implements MethodInterface
{

    /**
     * @var string
     */
    private $alias;

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var array
     */
    private $parameters;


    /**
     * @param string $alias
     * @param callable $callable
     * @param array|null $parameters
     */
    public function __construct($alias, $callable, $parameters = null)
    {
        $this->alias = $alias;
        $this->callable = $callable;
        $this->parameters = $parameters;
    }

    /**
     * Initialises a set of reflection methods from a class or object.
     *
     * Will register all public functions
     *
     * @param string|object $classOrObject
     * @param bool $loadParameters if set, will pre-load parameter data for all functions. Useful if exporting to a cache.
     * @param callable $aliasFactory this function should return the external alias of the method. Default is class.method. Is passed the \ReflectionMethod
     * @return ReflectionMethod[]
     */
    public static function createFrom($classOrObject, $loadParameters = true, $aliasFactory = null)
    {
        if ($aliasFactory === null) {
            // default aliasFactory is class.method
            $aliasFactory = function (\ReflectionMethod $m) {
                return "{$m->class}.{$m->name}";
            };
        }
        // load the class via reflections
        $reflectionClass = new \ReflectionClass($classOrObject);

        // avoid checking in the loop
        $isObject = is_object($classOrObject);

        // create an array of Ndm\JsonRpc2\Dispatch\ReflectionMethod
        $methods = array();
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            // skip non-static methods when passed a class name
            if (!$isObject && !$method->isStatic()) {
                continue;
            }

            // check and load parameters from reflection
            $parameters = null;
            if ($loadParameters) {
                $parameters = self::convertParameters($method->getParameters());
            }
            // create the instance and add to list
            $methods[] = new self($aliasFactory($method), array($classOrObject, $method->name), $parameters);
        }

        return $methods;
    }

    /**
     * Returns the method alias utilised by json-rpc calls
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param array $arguments
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     *
     * @return mixed
     */
    public function invoke($arguments)
    {
        if (!is_callable($this->callable)) {
            throw new RuntimeException("Method '{$this->alias}' doesn't have a valid callable");
        }
        $parameters = $this->getParameters();
        // check the parameters in the reflection against what was sent in the request
        $arguments = $this->checkArguments($parameters, $arguments);

        // return the result as an invoked call
        $result = null;
        try {
            $result = call_user_func_array($this->callable, $arguments);
        } catch (\Exception $ex) {
            // cover other exceptions as internal errors
            throw new RuntimeException("Execution of method '{$this->alias}'  failed.", 0, $ex);
        }
        return $result;
    }

    /**
     * Attempts to load the parameters for the provided method
     *
     * Uses lazy loading if they're not provided in the constructor
     *
     * @return array
     */
    private function getParameters()
    {
        if ($this->parameters === null) {
            // if the parameters aren't loaded, try to get via Reflections & Convert
            $reflectionParameters = $this->loadParameters($this->callable);
            $this->parameters = self::convertParameters($reflectionParameters);
        }
        return $this->parameters;
    }


    /**
     * Uses reflections to load the parameters from any callable.
     *
     * @param callable $callable
     * @throws RuntimeException
     * @return \ReflectionParameter[]
     */
    private function loadParameters($callable)
    {
        // detect the callable type, and try to use the correct reflections
        try {
            // check if callable is in the format class::method
            if (is_string($callable) && strpos($callable, "::") !== false) {
                $callable = explode("::", $callable, 1);
            }
            // check if the callable is array formatted (static, or instance method callables)
            if (is_array($callable)) {
                list ($classOrObject, $method) = $callable;
                // can't support format [ 'class', 'parent::method' ]
                if (strpos($method, "::") !== false) {
                    throw new RuntimeException();
                }
                // try to instantiate the reflection
                $reflection = new \ReflectionMethod($classOrObject, $method);
            } else {
                // closures, or global functions
                $reflection = new \ReflectionFunction($callable);
            }
            // get the parameters
            return $reflection->getParameters();
        } catch (\ReflectionException $rex) {
            // NOTE: could add $rex->getMessage() to the data of the internal error, however not suitable for production
            throw new RuntimeException("Failed to load parameters via reflections for callable.", 0, $rex);
        }
    }

    /**
     * Utility function that transforms a set of reflections parameters into an array suitable for use by this class.
     *
     * Returns an array formatted as [ {parameterName} => [ hasDefault => bool, default => mixed ] ]
     *
     * @static
     * @param \ReflectionParameter[] $reflectionParameters
     * @return array
     */
    private static function convertParameters($reflectionParameters)
    {
        $parameters = array();
        foreach ($reflectionParameters as $parameter) {
            $parameterData = array("hasDefault" => false, "default" => null);
            if ($parameter->isOptional()) {
                $parameterData['hasDefault'] = true;
                $parameterData['default'] = $parameter->getDefaultValue();
            }
            $parameters[$parameter->getName()] = $parameterData;
        }
        // return the parameters
        return $parameters;
    }

    /**
     * Checks the arguments, and provides an array suitable for invoking the method
     *
     * @param array $parameters
     * @param object|array $arguments
     * @throws InvalidArgumentException
     * @return array
     */
    private function checkArguments($parameters, $arguments)
    {
        // create an array of new arguments
        $newArguments = array();
        if (is_object($arguments)) { // associative list of parameter names and their values
            // get a list of parameters, and compare against provided arguments
            $additionalArgs = array_diff(
                array_keys($parameters),
                array_keys(get_object_vars($arguments))
            );
            if (count($additionalArgs) > 0) {
                throw new InvalidArgumentException("Additional named arguments were supplied that are not supported.");
            }
            foreach ($parameters as $parameterName => $parameter) {
                // check the object for the param
                if (property_exists($arguments, $parameterName)) {
                    $newArguments[] = $arguments->{$parameterName};
                } elseif ($parameter['hasDefault']) {
                    $newArguments[] = $parameter['default'];
                } else {
                    // if the parameter is not provided by arguments, throw an exception
                    throw new InvalidArgumentException("Required named parameter was not provided: {$parameterName}");
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
                } elseif ($parameter['hasDefault']) {
                    $newArguments[] = $parameter['default'];
                } else {
                    throw new InvalidArgumentException("Required positional parameter was not provided: {$currentArg}");
                }
            }
        }
        // return the new array of arguments
        return $newArguments;
    }
}
