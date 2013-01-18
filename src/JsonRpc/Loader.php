<?php

namespace JsonRpc;


/**
 * @author Nathan Muir
 * @version 2012-12-28
 */
class Loader
{

    /**
     * Registers a PSR-0 Compliant Autoload function
     */
    public static function register($extension = 'php')
    {
        spl_autoload_register(
            function ($className) use ($extension) {
                $className = ltrim($className, '\\');
                $fileName = '';
                if ($lastNsPos = strripos($className, '\\')) {
                    $namespace = substr($className, 0, $lastNsPos);
                    $className = substr($className, $lastNsPos + 1);
                    $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
                }
                $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.' . $extension;

                require $fileName;
            }
        );
    }

    public static function classmap()
    {
        // TODO create a list of require files

        $files = array();

        foreach ($files as $file) {
            require __DIR__ . DIRECTORY_SEPARATOR . $file;
        }
    }
}