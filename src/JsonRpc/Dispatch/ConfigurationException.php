<?php
namespace JsonRpc\Dispatch;

/**
 * Basic exception - not encapsulated by JsonRpc\Exception as it should only occur pre-init
 */
class ConfigurationException extends \Exception
{

}