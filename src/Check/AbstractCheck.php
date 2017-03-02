<?php

namespace TiB\PatchPal\Check;

abstract class AbstractCheck
{
    protected $basePath;
    protected $changeSet;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    abstract public function check($changeSet);
}
