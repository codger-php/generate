<?php

class ChefRecipe extends Codger\Generate\Recipe
{
    protected $template = 'main.html.twig';

    public function setTitle(string $title) : ChefRecipe
    {
        return $this->set('title', $title);
    }
}

class ChefMethod extends Codger\Generate\Recipe
{
    public function take(string $what) : ChefMethod
    {
        $this->template = 'methods/take.html.twig';
        $this->variables->ingredient = $what;
        return $this;
    }

    public function put(string $what, int $bowl = 1) : ChefMethod
    {
        $this->template = 'methods/put.html.twig';
        $this->variables->ingredient = $what;
        $this->variables->ordinal = $this->ordinal($bowl);
        return $this;
    }

    public function fold(string $what, int $bowl = 1) : ChefMethod
    {
        $this->put($what, $bowl);
        $this->template = 'methods/fold.html.twig';
        return $this;
    }

    public function add(string $what, int $bowl = 1) : ChefMethod
    {
        $this->put($what, $bowl);
        $this->template = 'methods/add.html.twig';
        return $this;
    }

    public function remove(string $what, int $bowl = 1) : ChefMethod
    {
        $this->put($what, $bowl);
        $this->template = 'methods/remove.html.twig';
        return $this;
    }

    public function combine(string $what, int $bowl = 1) : ChefMethod
    {
        $this->put($what, $bowl);
        $this->template = 'methods/combine.html.twig';
        return $this;
    }

    public function divide(string $what, int $bowl = 1) : ChefMethod
    {
        $this->put($what, $bowl);
        $this->template = 'methods/divide.html.twig';
        return $this;
    }

    public function dry(int $bowl = 1) : ChefMethod
    {
        $this->put('', $bowl);
        $this->template = 'methods/dry.html.twig';
        return $this;
    }

    public function liquefy(string $what) : ChefMethod
    {
        $this->put($what);
        $this->template = 'methods/liquefy.html.twig';
        return $this;
    }

    public function liquefyContents(int $bowl = 1) : ChefMethod
    {
        $this->put('', $bowl);
        $this->template = 'methods/liquefyContents.html.twig';
        return $this;
    }

    public function stir(int $number, int $bowl = 1) : ChefMethod
    {
        $this->put('', $bowl);
        $this->minutes = $number;
        $this->template = 'methods/stir.html.twig';
        return $this;
    }

    private function ordinal(int $ordinal) : string
    {
        switch (substr($ordinal, -1)) {
            case 1: return "{$ordinal}st";
            case 2: return "{$ordinal}nd";
            case 3: return "{$ordinal}rd";
            case 4: case 5: case 6: case 7: case 8: case 9: return "{$ordinal}th";
        }
    }
}

$twig = new Twig_Environment(new Twig_Loader_Filesystem(__DIR__));
$chef = new ChefRecipe($twig);
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

$chef->setTitle('Hello World Cake with Chocolate sauce')
    ->set('comment', <<<EOT
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
        (new ChefMethod($twig))->put('chocolate chips')->render(),
        (new ChefMethod($twig))->put('butter')->render(),
        (new ChefMethod($twig))->put('sugar')->render(),
        (new ChefMethod($twig))->put('beaten eggs')->render(),
        (new ChefMethod($twig))->put('flour')->render(),
        (new ChefMethod($twig))->put('baking powder')->render(),
        (new ChefMethod($twig))->put('cocoa powder')->render(),
    ])
    ->output('php://stdout');
