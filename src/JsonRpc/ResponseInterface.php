<?php

namespace JsonRpc;

/**
 * A simple interface which Response & ResponseError can implement
 *
 * @author Nathan Muir
 * @version 2012-12-24
 */
interface ResponseInterface
{

    /**
     * @return array
     */
    public function render();

    /**
     * @return string
     */
    public function __toString();

}