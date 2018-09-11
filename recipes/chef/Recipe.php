<?php

use Codger\Generate\Runner;
use Codger\Generate\Demo\ChefRecipe;
use Codger\Generate\Demo\ChefMethod;

return function (string ...$args) : ChefRecipe {
    $twig = new Twig_Environment(new Twig_Loader_Filesystem(__DIR__));
    $chef = new ChefRecipe($twig, ...Runner::arguments());
    $ingredients = new class($twig) extends ChefRecipe {
        protected $template = 'ingredients.html.twig';
    };
    $ingredients->set('ingredients', [
        [33, 'g', 'chocolate chips'],
        [100, 'g', 'butter'],
        [54, 'ml', 'double cream'],
        [2, 'pinches', 'baking powder'],
        [114, 'g', 'sugar'],
        [111, 'ml', 'beaten eggs'],
        [119, 'g', 'flour'],
        [32, 'g', 'chocolate powder'],
        [0, 'g', 'cake mixture'],
    ]);

    $method = new ChefMethod($twig);
    $chef->set('comment', <<<EOT
This prints hello world, while being tastier than Hello World Souffle. The main
chef makes a " world!" cake, which he puts in the baking dish. When he gets the
sous chef to make the "Hello" chocolate sauce, it gets put into the baking dish
and then the whole thing is printed when he refrigerates the sauce. When
actually cooking, I'm interpreting the chocolate sauce baking dish to be
separate from the cake one and Liquify to mean either melt or blend depending on
context.
EOT
        )
        ->set('ingredients', $ingredients->render())
        ->set('cooking', ['time' => 25, 'unit' => 'minutes'])
        ->set('oven', ['temperature' => 180])
        ->set('instructions', [
            $method->put('chocolate chips')->render(),
            $method->put('butter')->render(),
            $method->put('sugar')->render(),
            $method->put('beaten eggs')->render(),
            $method->put('flour')->render(),
            $method->put('baking powder')->render(),
            $method->put('cocoa powder')->render(),
            $method->stir(1)->render(),
            $method->combine('double cream')->render(),
            $method->stir(4)->render(),
            $method->liquefyContents()->render(),
            $method->pour()->render(),
        ]);
    if (!strlen($chef->get('title'))) {
        $chef->ask('What is the cake called?', function (string $title) : void {
            if (strlen($title)) {
                $this->setTitle($title);
            } else {
                $this->setTitle('Hello World Cake with Chocolate sauce');
            }
        });
    }
    $chef->options('Would sir like sauce with that?', ['y' => 'yes', 'n' => 'no'], function (string $answer) : void {
            if (in_array($answer, ['y', 'yes'])) {
                // Add sous-chef for chocolate sauce!
            }
        })
        ->output('php://stdout');
    return $chef;
};

