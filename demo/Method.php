<?php

namespace Codger\Demo;

use Codger\Generate\Recipe;

class Method extends Recipe
{
    public function __invoke() : void
    {
    }

    public function take(string $what) : Method
    {
        $this->_template = 'methods/take.html.twig';
        $this->_variables->ingredient = $what;
        return $this;
    }

    public function put(string $what, int $bowl = 1) : Method
    {
        $this->_template = 'methods/put.html.twig';
        $this->_variables->ingredient = $what;
        $this->_variables->ordinal = $this->ordinal($bowl);
        return $this;
    }

    public function fold(string $what, int $bowl = 1) : Method
    {
        $this->put($what, $bowl);
        $this->_template = 'methods/fold.html.twig';
        return $this;
    }

    public function add(string $what, int $bowl = 1) : Method
    {
        $this->put($what, $bowl);
        $this->_template = 'methods/add.html.twig';
        return $this;
    }

    public function remove(string $what, int $bowl = 1) : Method
    {
        $this->put($what, $bowl);
        $this->_template = 'methods/remove.html.twig';
        return $this;
    }

    public function combine(string $what, int $bowl = 1) : Method
    {
        $this->put($what, $bowl);
        $this->_template = 'methods/combine.html.twig';
        return $this;
    }

    public function divide(string $what, int $bowl = 1) : Method
    {
        $this->put($what, $bowl);
        $this->_template = 'methods/divide.html.twig';
        return $this;
    }

    public function dry(int $bowl = 1) : Method
    {
        $this->put('', $bowl);
        $this->_template = 'methods/dry.html.twig';
        return $this;
    }

    public function liquefy(string $what) : Method
    {
        $this->put($what);
        $this->_template = 'methods/liquefy.html.twig';
        return $this;
    }

    public function liquefyContents(int $bowl = 1) : Method
    {
        $this->put('', $bowl);
        $this->_template = 'methods/liquefyContents.html.twig';
        return $this;
    }

    public function stir(int $number, int $bowl = 1) : Method
    {
        $this->put('', $bowl);
        $this->_variables->minutes = $number;
        $this->_template = 'methods/stir.html.twig';
        return $this;
    }
    
    public function pour(int $bowl = 1, int $dish = 1) : Method
    {
        $this->_variables->bowl = $bowl;
        $this->_variables->dish = $dish;
        $this->_template = 'methods/pour.html.twig';
        return $this;
    }
    
    public function clean(int $bowl = 1) :Method
    {
        $this->put('', $bowl);
        $this->_template = 'methods/clean.html.twig';
        return $this;
    }
    
    public function agitate(string $what) : Method
    {
        $this->put($what);
        $this->_template = 'methods/agitate.html.twig';
        return $this;
    }
    
    public function dissolve(string $what) : Method
    {
        $this->put($what);
        $this->_template = 'methods/dissolve.html.twig';
        return $this;
    }
    
    public function refrigerate(int $number) : Method
    {
        $this->_variables->hours = $number;
        $this->_template = 'methods/refrigerate.html.twig';
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

