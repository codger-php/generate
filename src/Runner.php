<?php

namespace Codger\Generate;

use ReflectionFunction;

class Runner
{
    const ERROR_NO_RECIPE = 1;
    const ERROR_RECIPE_NOT_FOUND = 2;

    private $recipe;
    private $path;

    public function __construct(string $recipe, string $path = null)
    {
        $this->recipe = $recipe;
        $this->path = $path ?? getcwd();
    }

    public function run(string ...$argv) : void
    {
        if (file_exists("{$this->path}/recipes/{$this->recipe}/Recipe.php")) {
            $recipe = require "{$this->path}/recipes/{$this->recipe}/Recipe.php";
            $reflection = new ReflectionFunction($recipe);
            if ($reflection->getNumberOfRequiredParameters() > count($argv)) {
                $wanteds = $reflection->getParameters();
                $usage = call_user_func(function () use (&$wanteds) : string {
                    $param = array_shift($wanteds);
                    $out = '';
                    if ($param->isOptional()) {
                        $out .= ' [';
                    }
                    $out .= strtoupper($param->getName());
                    if ($wanteds) {
                        $out .= call_user_func(__FUNCTION__);
                    }
                    if ($param->isOptional()) {
                        $out .= ']';
                    }
                    return $out;
                }, $wanteds);
                fwrite(STDERR, "\nUsage: `$ vendor/bin/codger {$this->recipe} $usage`\n\n");
                if ($docComment = $reflection->getDocComment()) {
                    $docComment = preg_replace("@(^/\*\*\n|\n\s?\*/$)@", '', $docComment);
                    $docComment = preg_replace("@^\s?\*\s?@m", '', $docComment);
                    fwrite(STDERR, "$docComment\n\n");
                }
            } else {
                call_user_func($recipe, ...$argv)->process();
            }
        } else {
            fwrite(STDERR, "Recipe `{$this->recipe}` could not be found in `{$this->path}/recipes`, skipping...\n");
        }
    }

    public static function arguments() : array
    {
        global $argv;
        unset($argv[0], $argv[1]);
        return $argv;
    }
}

