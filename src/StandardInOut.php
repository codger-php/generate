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
        $input = null;
        do {
            if (isset($format)) {
                $input = fscanf(STDIN, $format);
                if (isset($input)) {
                    $input = $input[0];
                }
            } else {
                $input = trim(fgets(STDIN));
            }
        } while (!isset($input));
        return "$input";
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
