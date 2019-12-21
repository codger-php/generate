<?php

namespace Codger\Generate;

use Monolyth\Cliff;
use ReflectionFunction;
use stdClass;

class Command extends Cliff\Command
{
    use DefaultOptions;

    /** @var int */
    const ERROR_NO_RECIPE = 2;
    /** @var int */
    const ERROR_RECIPE_NOT_FOUND = 3;
    /** @var int */
    const ERROR_RECIPE_IS_NOT_A_RECIPE_EXCEPTION = 4;
    /** @var int */
    const ERROR_TWIG_ENVIRONMENT_NOT_SET = 5;

    /** @var array|null */
    private $_arguments;

    public function __construct(array $arguments = null)
    {
        parent::__construct($arguments, false);
        $this->_arguments = $arguments;
    }

    public function __invoke(string $recipe)
    {
        if (isset($this->_arguments)) {
            $argv = $this->_arguments;
        } else {
            $argv = $this->$_SERVER['argv'];
            array_shift($argv); // The executable. Ignore.
        }
        array_shift($argv); // This was the recipe name; no longer needed.
        $recipeClass = Recipe::toClassName($recipe);
        if (!class_exists($recipeClass)) {
            throw new RecipeNotFoundException("The recipe `$recipeClass` could not be autoloaded; does it exist?", self::ERROR_RECIPE_NOT_FOUND);
        }
        $recipe = new $recipeClass($argv);
        if (!($recipe instanceof Recipe)) {
            throw new RecipeIsNotARecipeException("The recipe `$recipeClass` does not extend `Codger\\Generate\\Recipe`; aborting.", self::ERROR_RECIPE_IS_NOT_A_RECIPE);
        }
        $recipe->execute();
        $recipe->process();
    }
}

