<?php

namespace Codger\Generate;

trait DefaultOptions
{
    /**
     * The base path where output is written to, relative to CWD.
     *
     * @var string
     */
    public $outputDir = '';

    /**
     * Whether or not to overwrite existing files.
     *
     * @var bool
     */
    public $replace = false;
}

