<?php

use Gentry\Gentry\Wrapper;
use Codger\Generate\Demo\ChefMethod;

/**
 * ChefMethod
 */

$twig = new Twig_Environment(new Twig_Loader_Filesystem('recipes/chef'));
$generator = Wrapper::createObject(ChefMethod::class, $twig);

return function () use ($twig, $generator): Generator {
    /** Take method */
    yield function () use ($twig, $generator) {
        $variables = ['ingredient' => 'yoghurt'];
        $template = $generator->take('yoghurt')->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Take yoghurt from refrigerator') !== false);
    };
    
    /** put method */
    yield function () use ($twig, $generator) {
        $variables = ['ingredient' => 'pepper', 'ordinal' => 1];
        $template = $generator->put('pepper')->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Put pepper into the mixing bowl') !== false);
    };
    
    /** fold method */
    yield function () use ($twig, $generator) {
        $variables = ['ingredient' => 'dough', 'ordinal' => 1];
        $template = $generator->fold('dough')->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Fold dough into the mixing bowl') !== false);
    };
    
    /** add method */
    yield function () use ($twig, $generator) {
        $variables = ['ingredient' => 'bacon', 'ordinal' => 1];
        $template = $generator->add('bacon')->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Add bacon') !== false);
    };
    
    /** remove method */
    yield function () use ($twig, $generator) {
        $variables = ['ingredient' => 'olives', 'ordinal' => 1];
        $template = $generator->remove('olives')->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Remove olives') !== false);
    };
    
    /** combine method */
    yield function () use ($twig, $generator) {
        $variables = ['ingredient' => 'cheese', 'ordinal' => 1];
        $template = $generator->combine('cheese')->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Combine cheese') !== false);
    };
    
    /** divide method  */
    yield function () use ($twig, $generator) {
        $variables = ['ingredient' => 'banana', 'ordinal' => 1];
        $template = $generator->divide('banana')->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Divide banana') !== false);
    };
    
    /** dry method  */
    yield function () use ($twig, $generator) {
        $variables = ['ordinal' => 1];
        $template = $generator->dry(1)->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Add dry ingredients') !== false);
    };
    
    /** liquefyContents method  */
    yield function () use ($twig, $generator) {
        $variables = ['ordinal' => 1];
        $template = $generator->liquefyContents(1)->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Liquefy contents of the mixing bowl') !== false);
    };
    
    /** stir method  */
    yield function () use ($twig, $generator) {
        $variables = ['ordinal' => 1, 'minutes' => 4];
        $template = $generator->stir(4)->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Stir for 4 minutes') !== false);
    };
    
    /** pour method  */
    yield function () use ($twig, $generator) {
        $variables = ['bowl' => 1, 'dish' => 1];
        $template = $generator->pour(1, 1)->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Pour contents of the mixing bowl into the baking dish') !== false);
    };
    
    /** clean method  */
    yield function () use ($twig, $generator) {
        $variables = ['ordinal' => 1];
        $template = $generator->clean(1)->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Clean the mixing bowl') !== false);
    };
    
    /** agitate method  */
    yield function () use ($twig, $generator) {
        $variables = ['ingredient' => 'chocolate'];
        $template = $generator->agitate('chocolate')->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Agitate the chocolate until dissolved') !== false);
    };
    
    /** dissolve method  */
    yield function () use ($twig, $generator) {
        $variables = ['ingredient' => 'chocolate'];
        $template = $generator->dissolve('chocolate')->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Dissolve the chocolate') !== false);
    };
    
    /** refrigerate method  */
    yield function () use ($twig, $generator) {
        $variables = ['hours' => 4];
        $template = $generator->refrigerate(4)->template;
        $result = $twig->render($template, $variables);
        assert(strpos($result, 'Refrigerate for 4 hours') !== false);
    };
};

