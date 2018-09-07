<?php

namespace Codger\Generate;

use Twig_Environment;
use StdClass;

abstract class Recipe
{
    protected $twig;
    protected $variables;

    /**
     * Constructor. Recipes must be constructed with a user-supplied
     * Twig_Environment, since we can't guess 
     */
    public function __construct(Twig_Environment $twig)
    {
        $this->variables = new StdClass;
        $this->twig = $twig;
    }

    public function set(string $name, $value) : Recipe
    {
        $this->variables->$name = $value;
        return $this;
    }

    public function render() : string
    {
        return $this->twig->render($this->template, (array)$this->variables);
    }

    public function output(string $filename) : Recipe
    {
        $output = $this->render();
        file_put_contents($filename, $output);
        return $this;
    }
}

