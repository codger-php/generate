<?php

namespace Coder\Generate\Demo;

use Codger\Generate\Recipe;
use Twig_Environment;

class ChefRecipe extends Recipe
{
    protected $template = 'main.html.twig';

    public function __construct(Twig_Environment $twig, string $title = '')
    {
        parent::__construct($twig);
        if (strlen($title)) {
            $this->setTitle($title);
        }
    }

    public function setTitle(string $title) : ChefRecipe
    {
        return $this->set('title', $title);
    }
}

