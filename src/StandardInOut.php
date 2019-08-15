<?php

namespace Codger\Generate;

/**
 * The standard input/output wrapper, using STDIN/OUT/ERROR.
 */
class StandardInOut implements InOut
{
    /**
     * Read character(s) from STDIN, optionally formatted by $format. If no
     * $format is specified, reads an entire line.
     *
     * @param string|null $format
     * @return string
     */
    public function read(string $format = null) : string
    {
        if (isset($format)) {
            return fscanf(STDIN, $format)[0];
        }
        return trim(fgets(STDIN));
    }

    /**
     * Write to STDOUT.
     *
     * @param string $output
     * @return void
     */
    public function write(string $output) : void
    {
        fwrite(STDOUT, $output);
    }

    /**
     * Write to STDERR.
     *
     * @param string $output
     * @return void
     * @TODO add some nice colours or something
     */
    public function error(string $output) : void
    {
        fwrite(STDERR, $output);
    }
}
