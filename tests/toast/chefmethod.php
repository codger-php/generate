<?php

use Gentry\Gentry\Wrapper;
use Codger\Generate\Demo\ChefMethod;

/**
 * Code helper
 */

$twig = new Twig_Environment(new Twig_Loader_Filesystem(__DIR__));
$generator = Wrapper::createObject(ChefMethod::class, $twig);

return function () use ($twig, $generator): Generator {    
    
    /** Take method tells us to get something from our fridge */
    yield function () use ($twig, $generator) {
        $variables = ['ingredient' => 'yoghurt'];
        $template = $generator->take('yoghurt')->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Take yoghurt from refrigerator') !== false);
    };
    
    /** Test put method */
    yield function () use ($generator) {
        assert($generator->put('blarps') instanceof ChefMethod);
    };
    
    /** Test fold method */
    yield function () use ($generator) {
        assert($generator->fold('blarps') instanceof ChefMethod);
    };
    
    /** Test add method */
    yield function () use ($generator) {
        assert($generator->add('blarps') instanceof ChefMethod);
    };
    
    /** Test remove method */
    yield function () use ($generator) {
        assert($generator->remove('blarps') instanceof ChefMethod);
    };
    
    /** Test combine method */
    yield function () use ($generator) {
        assert($generator->combine('blarps') instanceof ChefMethod);
    };
    
    /** Test divide method */
    yield function () use ($generator) {
        assert($generator->divide('blarps') instanceof ChefMethod);
    };
    
    /** Test dry method */
    yield function () use ($generator) {
        assert($generator->dry() instanceof ChefMethod);
    };
    
    /** Test liquefy method */
    yield function () use ($generator) {
        assert($generator->liquefy('blarps') instanceof ChefMethod);
    };
    
    /** Test liquefy method */
    yield function () use ($generator) {
        assert($generator->liquefy('blarps') instanceof ChefMethod);
    };
    
    /** Test liquefyContents method */
    yield function () use ($generator) {
        assert($generator->liquefyContents() instanceof ChefMethod);
    };
    
    /** Test stir method */
    yield function () use ($generator) {
        assert($generator->stir(1) instanceof ChefMethod);
    };
    
    /** Test pour method */
    yield function () use ($generator) {
        assert($generator->pour(1) instanceof ChefMethod);
    };
};

