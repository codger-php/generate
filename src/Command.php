<?php

namespace Codger\Generate;

use Monolyth\Cliff;
use stdClass;

/**
 * Generate code according to the supplied recipe.
 *
 * Usage:
 * $ vendor/bin/codger [OPTIONS] recipe
 */
class Command extends Cliff\Command
{
    use DefaultOptions;

    const ERROR_NO_RECIPE = 2;
    const ERROR_RECIPE_NOT_FOUND = 3;
    const ERROR_RECIPE_IS_NOT_A_RECIPE = 4;
    const ERROR_TWIG_ENVIRONMENT_NOT_SET = 5;

    /**
     * Constructor.
     *
     * @param array|null $_arguments
     * @return void
     */
    public function __construct(private ?array $_arguments = null)
    {
        parent::__construct($_arguments);
    }

    /**
     * Supply at least the name of the recipe. Note that all recipes _must_ be
     * namespaced in the `\Codger\` namespace. This is so you can autoload them
     * outside your regular codebase. You should omit the `codger` prefix on the
     * CLI.
     *
     * Per `monolyth/cliff` convention, you can use `:` or `/` as a namespace
     * separator on the CLI. Use `snake-case` for `snakeCase` syntax.
     *
     * @param string $recipe
     * @return void
     */
    public function __invoke(string $recipe) : void
    {
        if (isset($this->_arguments)) {
            $argv = $this->_arguments;
        } else {
            $argv = $_SERVER['argv'];
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
    }
}

