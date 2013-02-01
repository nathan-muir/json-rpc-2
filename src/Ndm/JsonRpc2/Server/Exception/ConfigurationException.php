<?php
namespace Ndm\JsonRpc2\Server\Exception;

/**
 * Basic exception - not encapsulated by Ndm\JsonRpc2\Exception as it should only occur pre-init
 */
class ConfigurationException extends RuntimeException
{

}
