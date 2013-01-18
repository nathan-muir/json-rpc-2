<?php

namespace JsonRpc\Dispatch;

/**
 * Basic dispatch system, allows any method that implements MethodInterface to be registered.
 *
 * Could be improved by the use of generics, eg MapDispatch<T implements MethodInterface>, AuthorisationInterface<T>
 *
 * @author Nathan Muir
 * @version 2013-01-18
 */
class MapDispatch implements DispatchInterface
{

    /**
     * @var MethodInterface[]
     */
    private $methods;

    /**
     * @var AuthorisationInterface|null
     */
    private $authorisation;

    /**
     * @param AuthorisationInterface $authorisation
     */
    public function __construct(AuthorisationInterface $authorisation = null)
    {
        $this->authorisation = $authorisation;
    }

    /**
     * @param string $alias
     * @param array $arguments
     *
     * @throws \JsonRpc\Exception_InvalidParams
     * @throws \JsonRpc\Exception_MethodNotFound
     * @throws \JsonRpc\Exception_Unauthorised
     * @throws \JsonRpc\Exception
     *
     * @return mixed
     */
    public function invoke($alias, $arguments)
    {
        // check that the method exists
        if (!isset($this->methods[$alias])) {
            throw new \JsonRpc\Exception_MethodNotFound();
        }

        // check authorisation if utilised
        if ($this->authorisation !== null && !$this->authorisation->isAuthorised($this->methods[$alias])) {
            throw new \JsonRpc\Exception_Unauthorised();
        }
        // all good, invoke the method
        return $this->methods[$alias]->invoke($arguments);
    }

    /**
     * @param MethodInterface $method
     * @throws ConfigurationException
     */
    public function register(MethodInterface $method)
    {
        // get the method alias and check for duplicates
        $alias = $method->getAlias();

        if (isset($this->methods[$alias])) {
            throw new ConfigurationException("Duplicate alias exists for {$alias}");
        }

        $this->methods[$alias] = $method;
    }

    /**
     * @param MethodInterface[] $methods
     */
    public function registerAll($methods)
    {
        foreach ($methods as $method) {
            $this->register($method);
        }
    }
}