<?php

namespace Codger\Demo;

use Codger\Generate\Recipe;
use Twig_Environment;
use Twig_Loader_Filesystem;

class Chef extends Recipe
{
    /** @var bool */
    public $sauce = false;

    /** @var string */
    protected $_template = 'main.html.twig';

    public function __invoke(string $title = null)
    {
        if (isset($title)) {
            $this->setTitle($title);
        }
        $this->_variables->souschefs = [];
        $twig = new Twig_Environment(new Twig_Loader_Filesystem(__DIR__));
        $this->setTwigEnvironment($twig);
        $ingredients = new class() extends Chef {
            protected $_template = 'ingredients.html.twig';
        };
        $ingredients->setTwigEnvironment($twig);
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
        
        $method = new Method;
        $method->setTwigEnvironment($twig);
        $this->set('comment', <<<EOT
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
        if (!isset($title)) {
            $this->ask('What is the cake called?', function (string $title) : void {
                if (strlen($title)) {
                    $this->setTitle($title);
                } else {
                    $this->setTitle('Hello World Cake with Chocolate sauce');
                }
            });
        }
        if ($this->sauce) {
            $ingredients = new class() extends Chef {
                protected $_template = 'ingredients.html.twig';
            };
            $ingredients->setTwigEnvironment($twig);
            $ingredients->set('ingredients', [
                [111, 'g', 'sugar'],
                [108, 'ml', 'hot water'],
                [108, 'ml', 'heated double cream'],
                [101, 'g', 'dark chocolate'],
                [72, 'g', 'milk chocolate']
            ]);
            $sauce = new Chef;
            $sauce->setTwigEnvironment($twig);
            $sauce->setTitle('Chocolate sauce')
                ->set('ingredients', $ingredients->render())
                ->set('instructions', [
                    $method->clean(1)->render(),
                    $method->put('sugar')->render(),
                    $method->put('hot water')->render(),
                    $method->put('heated double cream')->render(),
                    $method->dissolve('sugar')->render(),
                    $method->agitate('sugar')->render(),
                    $method->liquefy('dark chocolate')->render(),
                    $method->put('dark chocolate')->render(),
                    $method->liquefy('milk chocolate')->render(),
                    $method->put('milk chocolate')->render(),
                    $method->liquefyContents(1)->render(),
                    $method->pour(1, 2)->render(),
                    $method->refrigerate(1)->render()
                ]);
            $this->addSousChef($sauce);
        }
        $this->output(sys_get_temp_dir().'/chef');
    }

    protected function setTitle(string $title) : Chef
    {
        return $this->set('title', $title);
    }

    protected function addSousChef(Chef $recipe) : Chef
    {
        $this->_variables->souschefs[] = $recipe->render();
        return $this;
    }
}

