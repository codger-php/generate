<?php

use Gentry\Gentry\Wrapper;
use Codger\Generate\Demo\ChefRecipe;

/**
 * ChefRecipe
 */

$twig = new Twig_Environment(new Twig_Loader_Filesystem('recipes/chef'));
$generator = Wrapper::createObject(ChefRecipe::class, $twig, 'Demo Recept');

return function () use ($twig, $generator) : Generator {
    /** Test methods specific to ChefRecipe */
    yield function () use ($twig, $generator) : Generator {    
        /** setTitle modifies the title after which we can retrieve it with the get method */
        yield function () use ($generator) {
            $generator->setTitle('blarps');
            assert($generator->get('title') === 'blarps');
        };
        
        /** addSousChef adds a souschef after which we can retrieve it with the get method */
        yield function () use ($twig, $generator) {
            $chef = $generator->addSousChef(new ChefRecipe($twig, 'Knoflooksaus'));
            assert(strpos($chef->get('souschefs')[0], 'Knoflooksaus') !== false);
        };
    };

    /** Test base Recipe methods */
    yield function () use ($generator) : Generator {    
        /** Test set method */
        yield function () use ($generator) {
            assert($generator->set('blarps', 'blarps') instanceof ChefRecipe);
        };
        
        /** Test get method */
        yield function () use ($generator) {
            assert(is_string($generator->get('blarps')));
        };
        
        /** Test render method */
        yield function () use ($generator) {
            assert(is_string($generator->render()));
        };
        
        /** Test ask method */
        yield function () use ($generator) {
            $question = $generator->ask('We\'re going to test this?', function ($answer) {
                $this->set('answer', $answer);
            });
            assert($question instanceof ChefRecipe);
        };
        
        /** Test options method */
        yield function () use ($generator) {
            $options = $generator->options('Want some coffee?', ['y' => 'Yes', 'n' => 'No'], function () {
                return 'Have some coffee!';
            });
            assert($options instanceof ChefRecipe);
        };

        /** Test output method */
        yield function () use ($generator) {
            assert($generator->output('blarps') instanceof ChefRecipe);
        };
        
        /** Test process method */
        yield function () use ($generator) {
            assert($generator->process() === null);
        };
        
        /** Test delegate method */
        yield function () use ($generator) {
            assert($generator->delegate('chef') instanceof ChefRecipe);
        };
    };
};

