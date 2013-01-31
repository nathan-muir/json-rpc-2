<?php

namespace Ndm\JsonRpc2\Core;

/**
 *
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
