<?php

namespace Codger\Generate;

class Generator
{
    private $config;

    public function __construct(string $recipe)
    {
        $this->config = json_decode(file_get_contents($recipe));
        var_dump($this->config);
    }
}

