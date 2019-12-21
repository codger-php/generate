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

    /** @var array */
    private $arguments = [];

    public function __construct(array $arguments = null, bool $strict = true)
    {
        parent::__construct($arguments, $strict);
        $this->arguments = $arguments;
    }

    public function __invoke(string $recipe)
    {
        $argv = $this->arguments;
        $recipeClass = Recipe::toClassName($recipe);
        if (!class_exists($recipeClass)) {
            throw new RecipeNotFoundException("The recipe `$recipeClass` could not be autoloaded; does it exist?", self::ERROR_RECIPE_NOT_FOUND);
        } else {
            $recipe = new $recipeClass($argv);
            $arguments = $recipe->getOperands();
            array_shift($arguments); // script name
            $recipe(...$arguments);
            $recipe->process();
        }
    }
}

