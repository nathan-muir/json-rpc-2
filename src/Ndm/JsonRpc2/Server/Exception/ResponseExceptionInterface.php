<?php
namespace Ndm\JsonRpc2\Server\Exception;

/**
 * An interface for Client Code Exceptions to implement.
 *
 * Allows translation to a JSON-RPC Response Error Object.
 *
 */
interface ResponseExceptionInterface
{

    /**
     * @return int
     */
    public function getErrorCode();

    /**
     * @return string
     */
    public function getErrorMessage();

    /**
     * @return mixed
     */
    public function getErrorData();
}
