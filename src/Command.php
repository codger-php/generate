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

    /** @var int */
    const ERROR_NO_RECIPE = 2;
    /** @var int */
    const ERROR_RECIPE_NOT_FOUND = 3;
    /** @var int */
    const ERROR_RECIPE_IS_NOT_A_RECIPE = 4;
    /** @var int */
    const ERROR_TWIG_ENVIRONMENT_NOT_SET = 5;

    /** @var array|null */
    private ?array $_arguments;

    /**
     * Constructor.
     *
     * @param array|null $arguments
     * @return void
     */
    public function __construct(array $arguments = null)
    {
        parent::__construct($arguments);
        $this->_arguments = $arguments;
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
    }
}

