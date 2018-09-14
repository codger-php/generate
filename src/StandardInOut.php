<?php

namespace Codger\Generate;

class StandardInOut implements InOut
{
    public function read(string $format = null) : string
    {
        if (isset($format)) {
            return fscanf(STDIN, $format)[0];
        }
        return trim(fgets(STDIN));
    }

    public function write(string $output) : void
    {
        fwrite(STDOUT, $output);
    }

    public function error(string $output) : void
    {
        fwrite(STDERR, $output);
    }
}
