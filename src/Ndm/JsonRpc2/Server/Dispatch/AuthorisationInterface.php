<?php
namespace Ndm\JsonRpc2\Server\Dispatch;

/**
 * This interface provides a mechanism in which an implementation can control access to
 * methods executed by MapDispatch.
 *
 * The design constraint is that implementations [of this interface] may require their own implementation of MethodInterface.
 *
 * @author Nathan Muir
 * @version 2013-01-18
 */
interface AuthorisationInterface
{

    /**
     * @param MethodInterface $method
     * @return bool
     */
    public function isAuthorised(MethodInterface $method);
}