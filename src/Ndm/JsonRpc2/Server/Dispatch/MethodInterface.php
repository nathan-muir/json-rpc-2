<?php


namespace Ndm\JsonRpc2\Server\Dispatch;

/**
 * An interface used by MapDispatch to interact with methods
 *
 */
interface MethodInterface
{

    /**
     * Returns the method alias utilised by json-rpc calls
     * @return string
     */
    public function getAlias();

    /**
     * @param array $arguments
     *
     * @throws \Ndm\JsonRpc2\Server\Exception\InvalidArgumentException
     * @throws \Ndm\JsonRpc2\Server\Exception\RuntimeException
     */
    public function invoke($arguments);
}
