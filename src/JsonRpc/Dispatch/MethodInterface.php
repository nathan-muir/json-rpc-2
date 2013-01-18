<?php


namespace JsonRpc\Dispatch;

/**
 * An interface used by MapDispatch to interact with methods
 *
 * @author Nathan Muir
 * @version 2012-12-28
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
     * @throws \JsonRpc\Exception_InvalidParams
     * @throws \JsonRpc\Exception_InternalError
     */
    public function invoke($arguments);
}