<?php

namespace Codger\Generate;

class StandardInOut implements InOut
{
    public function read(string $format = null)
    {
        if (isset($format)) {
            return fscanf(STDIN, $format)[0];
        }
        return trim(fgets(STDIN));
    }

    public function write(string $output)
    {
        fwrite(STDOUT, $output);
    }

    public function error(string $output)
    {
        fwrite(STDERR, $output);
    }
}
