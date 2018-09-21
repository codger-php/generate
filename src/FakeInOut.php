<?php

namespace Codger\Generate;

/**
 * Fake input/ouput handler.
 */
class FakeInOut implements InOut
{
    /** @var array */
    private $stack = [];
    /** @var string */
    private $output = '';

    /**
     * Fake input reader. Shifts elements off the stack.
     *
     * @param string|null $format Optional format
     * @return string
     */
    public function read(string $format = null) : string
    {
        $input = array_shift($this->stack);
        if (isset($format, $input)) {
            $input = sprintf($format, $input);
        }
        return $input ?? '';
    }

    /**
     * Dummy writer.
     *
     * @param string $output
     * @return void
     * @see Codger\Generate\FakeInOut::flush
     */
    public function write(string $output) : void
    {
        $this->output .= $output;
    }

    /**
     * Dummy error handler.
     *
     * @param string $error
     * @return void
     */
    public function error(string $error) : void
    {
        $this->write($error);
    }

    /**
     * Add an expectation, i.e. input prompted by e.g.
     * `Codger\Generate\Recipe::answer`.
     *
     * @param string $expectation Expected input
     * @return Codger\Generate\FaceInOut Itself, for chaining.
     */
    public function expect(string $expectation) : FakeInOut
    {
        $this->stack[] = $expectation;
        return $this;
    }

    /**
     * Returns the output so far, and resets the cache.
     *
     * @return string
     */
    public function flush() : string
    {
        $output = $this->output;
        $this->output = '';
        return $output;
    }
}

