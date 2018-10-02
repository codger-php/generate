<?php

namespace Codger\Generate;

use ReflectionFunction;

class Bootstrap
{
    const ERROR_NO_RECIPE = 1;
    const ERROR_RECIPE_NOT_FOUND = 2;

    /** @var string */
    private $recipe;
    /** @var string */
    private $path;
    /** @var array */
    private $options = [];

    /**
     * @param string $recipe The name of the recipe to run.
     * @param string $path|null Optional path to search for the recipe in.
     */
    public function __construct(string $recipe, string $path = null)
    {
        $this->recipe = $recipe;
        $this->path = $path ?? getcwd();
    }

    /**
     * Run the recipe.
     *
     * @param mixed ...$argv Arguments passed from CLI.
     * @return void
     */
    public function run(...$argv) : void
    {
        $file = "{$this->path}/recipes/{$this->recipe}/Recipe.php";
        if (count(explode('/', $this->recipe)) >= 3) {
            $vendor = substr($this->recipe, 0, strrpos($this->recipe, '/'));
            $recipe = substr($this->recipe, strrpos($this->recipe, '/') + 1);
            $old = $file;
            $file = "{$this->path}/vendor/$vendor/recipes/$recipe/Recipe.php";
            if (!file_exists($file)) {
                $file = $old;
            }
        }
        if (file_exists($file)) {
            $recipe = require $file;
            $reflection = new ReflectionFunction($recipe);
            $wanteds = $reflection->getParameters();
            if ($reflection->getNumberOfRequiredParameters() > count($argv)) {
                $usage = call_user_func($tmp = function () use (&$tmp, &$wanteds) : string {
                    $param = array_shift($wanteds);
                    $out = ' ';
                    if ($param->isOptional()) {
                        $out .= '[';
                    }
                    if ($param->isVariadic()) {
                        $out .= '...';
                    }
                    $out .= strtoupper($param->getName());
                    if ($wanteds) {
                        $out .= $tmp();
                    }
                    if ($param->isOptional()) {
                        $out .= ']';
                    }
                    return $out;
                }, $wanteds);
                fwrite(STDERR, "\nUsage: `$ vendor/bin/codger {$this->recipe}$usage`\n\n");
                if ($docComment = $reflection->getDocComment()) {
                    $docComment = preg_replace("@(^/\*\*\n|\n\s?\*/$)@", '', $docComment);
                    $docComment = preg_replace("@^\s?\*\s?@m", '', $docComment);
                    fwrite(STDERR, "$docComment\n\n");
                }
            } else {
                if ($wanteds && end($wanteds)->isVariadic()) {
                    $this->setOptions(array_splice($argv, count($wanteds) - 1));
                }
                $recipe->setPath($this->path);
                $recipe->call($this, ...$argv)->process();
            }
        } else {
            fwrite(STDERR, "Recipe `{$this->recipe}` could not be found in `{$this->path}/recipes`, skipping...\n");
        }
    }

    /**
     * Helper to return cleaned passed arguments.
     *
     * @return array
     */
    public static function arguments() : array
    {
        $args = $GLOBALS['argv'];
        unset($args[0], $args[1]);
        putenv("CODGER_DRY=1");
        foreach ($args as $key => $value) {
            if ($value === '-w') {
                putenv("CODGER_DRY=0");
                unset($args[$key]);
                break;
            }
        }
        return array_values($args);
    }

    /**
     * Check if an option (or its negation) was specified as an argument.
     *
     * @param string $name
     * @return bool
     */
    public function hasOption(string $name) : bool
    {
        return in_array($name, $this->options) || in_array("^$name", $this->options);
    }

    /**
     * Check if an option (not its negation) was specified as an argument.
     *
     * @param string $name
     * @return bool
     */
    public function askedFor(string $name) : bool
    {
        return in_array($name, $this->options);
    }

    /**
     * Set default options. Defaults will only be added if not specified yet
     * (note: negations will not be overridden!).
     *
     * @param string ...$names List of option names.
     * @return void
     */
    public function defaults(string ...$names) : void
    {
        array_walk($names, function ($name) {
            if (!$this->hasOption($name)) {
                $this->options[] = $name;
            }
        });
    }
    
    /**
     * Set options programmatically. Mostly used internally.
     */
    public function setOptions(array $options) : void
    {
        $this->options = $options;
    }
}

