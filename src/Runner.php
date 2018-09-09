<?php

namespace Codger\Generate;

class Runner
{
    const ERROR_NO_RECIPE = 1;
    const ERROR_RECIPE_NOT_FOUND = 2;

    public function __construct(Recipe $recipe)
    {
        $this->recipe = $recipe;
    }

    public function run() : void
    {
        $this->recipe->process();
    }
}

