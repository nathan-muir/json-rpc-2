<?php

namespace Ndm\JsonRpc2\Server;

/**
 * Helper class for easy set up of typical transport and dispatch scenarios.
 */
class HttpServer {

    /**
     * Registers all arguments with a dispatch system, and returns a new server.
     *
     * @param object|string|array $methods
     *
     * @throws Exception\ConfigurationException
     *
     * @return Server
     */
    public static function register($methods){
        // create the dispatch and register all the methods
        $dispatch = new Dispatch\MapDispatch();
        // iterate through all arguments
        foreach(func_get_args() as $methods){
            if (is_array($methods)){
                // if it's an array, assume its a map of [ alias => callable ]
                foreach ($methods as $alias=>$callable){
                    $dispatch->register(new Dispatch\ReflectionMethod($alias, $callable));
                }
            } else {
                // otherwise it's a class name or object.
                $dispatch->register(Dispatch\ReflectionMethod::createFrom($methods));
            }
        }
        // create a basic transport to use
        $transport = new Transport\HttpTransport();
        // create and return the server
        return new Server($transport, $dispatch);
    }
}