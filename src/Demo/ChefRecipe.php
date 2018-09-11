<?php

namespace Codger\Generate\Demo;

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
        $this->variables->souschefs = [];
    }

    public function setTitle(string $title) : ChefRecipe
    {
        return $this->set('title', $title);
    }

    public function addSousChef(ChefRecipe $recipe) : ChefRecipe
    {
        $this->variables->souschefs[] = $recipe->render();
        return $this;
    }
}

