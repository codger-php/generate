<?php

namespace Codger\Generate\Demo;

use Codger\Generate\Recipe;

class ChefMethod extends Recipe
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
        $this->variables->minutes = $number;
        $this->template = 'methods/stir.html.twig';
        return $this;
    }
    
    public function pour(int $bowl = 1, int $dish = 1) : ChefMethod
    {
        $this->variables->bowl = $bowl;
        $this->variables->dish = $dish;
        $this->template = 'methods/pour.html.twig';
        return $this;
    }
    
    public function clean(int $bowl = 1) :ChefMethod
    {
        $this->put('', $bowl);
        $this->template = 'methods/liquefyContents.html.twig';
        return $this;
    }
    
    public function agitate(string $what) : ChefMethod
    {
        $this->put($what);
        $this->template = 'methods/agitate.html.twig';
        return $this;
    }
    
    public function dissolve(string $what) : ChefMethod
    {
        $this->put($what);
        $this->template = 'methods/dissolve.html.twig';
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

