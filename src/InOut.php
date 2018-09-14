<?php

namespace Codger\Generate;

interface InOut
{
    public function read(string $format = null);
    public function write(string $output);
    public function error(string $output);
}

