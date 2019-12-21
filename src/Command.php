<?php

namespace Codger\Generate;

use Monolyth\Cliff;
use ReflectionFunction;
use stdClass;

class Command extends Cliff\Command
{
    use DefaultOptions;

    /** @var int */
    const ERROR_NO_RECIPE = 1;
    /** @var int */
    const ERROR_RECIPE_NOT_FOUND = 2;

    public function __invoke(string $recipe)
    {
        global $argv;
        $recipeClass = Recipe::toClassName($recipe);
        unset($argv[1]);
        $recipe = new $recipeClass($argv);
        $arguments = $recipe->getOperands();
        array_shift($arguments); // script name
        $recipe(...$arguments);
    }
}

