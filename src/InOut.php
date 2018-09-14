<?php

namespace Codger\Generate;

interface InOut
{
    public function read(string $format = null) : string;
    public function write(string $output) : void;
    public function error(string $output) : void;
}

