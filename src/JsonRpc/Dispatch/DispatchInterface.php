<?php

namespace JsonRpc\Dispatch;

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
     * @throws \JsonRpc\Exception_InvalidParams
     * @throws \JsonRpc\Exception_MethodNotFound
     * @throws \JsonRpc\Exception
     *
     * @return mixed
     */
    public function invoke($alias, $arguments);

}