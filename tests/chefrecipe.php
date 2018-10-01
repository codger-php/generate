<?php

use Gentry\Gentry\Wrapper;
use Codger\Generate\Demo\ChefRecipe;

/**
 * ChefRecipe
 */

$twig = new Twig_Environment(new Twig_Loader_Filesystem('recipes/chef'));
$generator = Wrapper::createObject(ChefRecipe::class, $twig, 'Demo Recept');
$inout = Wrapper::createObject(Codger\Generate\FakeInOut::class);
Codger\Generate\Recipe::setInOut($inout);

return function () use ($twig, $generator, $inout) : Generator {
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
    yield function () use ($generator, $inout) : Generator {
        /** The set method sets a variable */
        yield function () use ($generator) {
            assert($generator->set('brood', 'bruin') instanceof ChefRecipe);
        };
        
        /** After which we can retrieve it using the get method */
        yield function () use ($generator) {
            assert($generator->get('brood') === 'bruin');
        };
        
        /** The render methods returns a string with our template + information */
        yield function () use ($generator) {
            $generator->setTitle('Testing rendering');
            assert(strpos($generator->render(), 'Testing rendering') !== false);
        };
        
        /** The ask method will throw a question after which it succesfully runs the callback */
        yield function () use ($generator, $inout) {
            $inout->expect('mayonaise');
            $generator->ask('What sauce to go with your fries, sir?', function ($answer) {
                $this->set('sauce', $answer);
            });
            assert($generator->get('sauce') === 'mayonaise');
        };
        
        /** The options method allows us to present options and pass the answer to the callback */
        yield function () use ($generator, $inout) {
            $inout->expect('b');
            $options = ['b' => 'black', 'c' => 'cappucino'];
            $generator->options('How do you like your coffee?', $options, function ($answer) use ($options) {
                $this->set('coffee', $options[$answer]);
            });
            assert($generator->get('coffee') === 'black');
        };

        /** The output method writes to a file and we can confirm that the file exists */
        yield function () use ($generator) {
            $file = sys_get_temp_dir().'/cooking.log';
            if (file_exists($file)) {
                unlink($file);
            }
            $generator->output($file);
            $generator->process();
            assert(file_exists($file));
        };
        
        /** Process returns null */
        yield function () use ($generator) {
            putenv("CODGER_DRY=1");
            assert($generator->process() === null);
        };
        
        /** The delegate method can refer to a recipe and return it */
        yield function () use ($generator, $inout) {
            $inout->expect('foo');
            $inout->expect('n');
            assert($generator->delegate('chef') instanceof ChefRecipe);
        };
    };
};
