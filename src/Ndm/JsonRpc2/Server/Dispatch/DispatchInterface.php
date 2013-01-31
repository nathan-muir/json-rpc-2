<?php

namespace Ndm\JsonRpc2\Server\Dispatch;

/**
 * Base interface for dispatch systems to implement.
 *
 * Can be used to control implementation of method & parameter binding
 *
 */
interface DispatchInterface
{

    /**
     * Invokes the method $alias, with the provided arguments.
     *
     * Implementers must be sure that all exceptions that are propagated subclass from RuntimeException.
     *
     * Exceptions caused in the Dispatch implementation itself should subclass RuntimeException (and implement
     * ResponseExceptionInterface if they need to be reported to clients), and exceptions sent from the invoked
     * functions should be wrapped in a RuntimeException. If the wrapped exception implements ResponseExceptionInterface
     * it wll also be exposed.
     *
     * @param string $alias
     * @param array $arguments
     *
     * @throws \Ndm\JsonRpc2\Server\Exception\RuntimeException
     *
     * @return mixed
     */
    public function invoke($alias, $arguments);

}
