<?php

namespace Ndm\JsonRpc2;

/**
 *
 * @author Nathan Muir
 * @version 2012-12-24
 */
interface ExportableInterface
{

    /**
     * @return mixed
     */
    public function toJsonNatives();

    /**
     * @return string
     */
    public function toJson();

}