<?php

use Gentry\Gentry\Wrapper;
use Codger\Generate\Demo\ChefRecipe;

/**
 * ChefRecipe
 */

$twig = new Twig_Environment(new Twig_Loader_Filesystem('recipes/chef'));
$generator = Wrapper::createObject(ChefRecipe::class, $twig);

return function () use ($twig, $generator): Generator {    
    /** Dummy test */
    yield function () {
        assert(true === true);
    };
};

