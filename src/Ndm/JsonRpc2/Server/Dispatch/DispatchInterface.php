<?php

namespace Ndm\JsonRpc2\Server\Dispatch;

/**
 * Base interface for dispatch systems to implement.
 *
 * Can be used to control implementation of method & parameter binding
 *
 * @author Nathan Muir
 * @version 2012-12-24
 */
interface DispatchInterface
{

    /**
     * @param string $alias
     * @param array $arguments
     *
     * @throws \Ndm\JsonRpc2\Exception_InvalidParams
     * @throws \Ndm\JsonRpc2\Exception_MethodNotFound
     * @throws \Ndm\JsonRpc2\Exception
     *
     * @return mixed
     */
    public function invoke($alias, $arguments);

}