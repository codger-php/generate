<?php

namespace Codger\Generate;

class Runner
{
    const ERROR_NO_RECIPE = 1;
    const ERROR_RECIPE_NOT_FOUND = 2;

    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function run(string ...$argv) : void
    {
        $recipe = require $this->path;
        $recipe->process();
    }

    public static function arguments() : array
    {
        global $argv;
        unset($argv[0], $argv[1]);
        return $argv;
    }
}

